import { ActionRequest, ActionResponse } from "./types"
import { makeRequest } from "./utils/make-request"

const setupAction = async (
  key: string|ActionRequest,
  data?: object|{[key: string]: string},
  config?: object|{[key: string]: string}
): Promise<any> => {

  const request: ActionRequest = {
    url: `actions/${key}`,
    data,
    config
  }

  const firstResponse: ActionResponse = await makeRequest(request)

  return new Proxy({
    request,
    responses: [firstResponse],
    async fresh() {
      const freshResponse: ActionResponse = await makeRequest({ ...request, data })
      this.responses.push(freshResponse)
    }
  }, {
    get(target, p) {
      const latestResponse = target.responses.reverse()[0]
      return target[p] || latestResponse.data[p] || null
    }
  })
}

export default new Proxy({
  //
}, {
  get: (target, p, receiver) => {

    if (p === 'get') {
      return async (key: string, data?: any) => await setupAction(key, data)
    }

    const key = String(p)

    return async (data: any) => await setupAction(key, data)
  }
})
