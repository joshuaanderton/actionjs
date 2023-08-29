import { ActionRequest, ActionResponse } from "./types"
import { makeRequest } from "./utils/make-request"

export default new class {
  constructor() {

  }

  async call(key: string, params: object): Promise<ActionResponse> {
    const request: ActionRequest = {
      url: `/api/actionjs/${key}`,
      data: params
    }

    return await makeRequest(request)
  }
}
