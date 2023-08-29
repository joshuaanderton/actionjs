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

  const firstResponse = await makeRequest(request)

  return new Proxy({
    ...(firstResponse.data || {}),
    request,
    responses: [firstResponse],
    async fresh() {
      const freshResponse = await makeRequest({ ...request, data })
      this.responses.push(freshResponse)
      Object.entries(freshResponse)
        .filter(key => ['request', 'responses'].includes(String(key)) === false)
        .forEach((key, val) => {
          this[key] = val
        })
    }
  }, {
    get(target, p, receiver) {
      if (p === 'fresh') {
        return target.fresh
      }
      if (p === 'debug') {
        return target
      }
      if (p === 'data') {
        return target.responses.reverse()[0].data
      }
      return target.responses.reverse()[0].data[p] || target[p] || null
    }
  })
}

export default new Proxy({
  //
}, {
  get: (target, p, receiver) => {
    const actionKey = String(p)

    // @ts-ignore
    const existing = target[actionKey]

    if (existing) {
      return (data: any) => existing
    }

    return async (data: any) => {
      const action = await setupAction(actionKey, data)
      return receiver[actionKey] = action
    }
  }
})
