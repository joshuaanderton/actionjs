import localForage from 'localforage'
import { ActionRequest, ActionResponse } from '../types'

// LF uses localStorage in browsers with no IndexedDB or WebSQL support
// browser support: https://github.com/localForage/localForage/wiki/Supported-Browsers-Platforms

localForage.config({
  name: 'actionjs',
  version: 1.0,
  storeName: 'actionjs',
  description : 'Local storage for actionjs query caching',
})

const store: LocalForage = localForage.createInstance({ name: 'default' })

export const storeKey = ({ url, params = null }: {url: string, params?: {[key: string]: string}|null}) => {
  let orderedParams: {[key: string]: string} = {}

  if (params) {
    Object.keys(params).sort().map(key => {
      orderedParams[key] = params[key]
    })
  }

  return `${url}${JSON.stringify(orderedParams)}`
}

export const findOrNew = async (req: ActionRequest, handleRequest: (req: ActionRequest) => Promise<ActionResponse>): Promise<ActionResponse> => {

  const key = storeKey(req),
        fromStore = await store.getItem(key)

  let res: ActionResponse


  if (typeof fromStore === 'string') {

    res = JSON.parse(fromStore)

    // Make new req for next time
    handleRequest(req).then((res: ActionResponse) => (
      store.setItem(key, JSON.stringify(res)
    )))

    return res
  }

  return await handleRequest(req).then(res => {
    store.setItem(key, JSON.stringify(res))
    return res
  })
}

export default store
