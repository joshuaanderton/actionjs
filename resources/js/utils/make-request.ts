import axios from 'axios'
import { debounce } from 'lodash'
import { ActionRequest, ActionRequestConfig, ActionResponse, ActionQueueItem, AxiosRequestOptions } from '../types'
import store, { storeKey } from './store'

const debounceQueueMakeRequestWait: number = 500
//const maxQueueItems: number = 20

let queue: Array<ActionQueueItem> = []

export const queueMakeRequest = async (request: ActionRequest) => {

    const key = storeKey(request),
          fromStore = await store.getItem(key)

    return new Promise((resolve, reject) => {
        const queueItem: ActionQueueItem = { key, request, resolve, reject }

        // Get item from store or queue request
        if (fromStore !== null) {
            queueItem.resolve(fromStore)
        } else {
            queue.push(queueItem)
            debounceQueueMakeRequest()
        }
    })
}

export const makeRequest = (request: ActionRequest): Promise<ActionResponse> => {

    const key = storeKey(request),
          { url } = request

    let { headers, ...options } = getRequestOptions(request)

    const instance = axios.create({
        baseURL: '/api/actionjs/',
        timeout: 1000,
        headers
    })

    const axiosRequest = instance({
        url,
        ...options,
        //paramsSerializer: () => Qs.stringify(params, {arrayFormat: 'brackets'}),
        //onUploadProgress: (progressEvent) => {},
        //onDownloadProgress: (progressEvent) => {},
    })

    axiosRequest.catch(error => {
        console.log('error', error)
        store.removeItem(key)
    })

    return axiosRequest
}

const debounceQueueMakeRequest = debounce(() => (

    makeRequest({
        url: 'batch-requests',
        data: {
            queue: JSON.stringify(queue)
        },
        config: {
            method: 'post'
        }
    })
    .then(({ data }: ActionResponse) => (
        (data.batch || []).forEach((resp: { key: string, error?: string|null }) => {
            const { key, error = null } = resp,
                  queueItem = queue.filter(qi => qi.key === key)[0]

            if (error) {
                store.removeItem(key)
                queueItem.reject(error)
            } else {
                store.setItem(key, resp)
                queueItem.resolve(resp)
            }
        })
    ))
    .catch(error => (
        queue.forEach(({ key, reject }) => {
            store.removeItem(key)
            reject(error)
        })
    ))
    .then(() => queue = [])

), debounceQueueMakeRequestWait)

const getRequestOptions = ({ method = 'post', data, headers = {} }: ActionRequestConfig): AxiosRequestOptions => {

    headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': csrfToken(),
        ...headers
    }

    if (method === 'get') {
        // @ts-ignore
        headers['Content-Type'] = 'application/json'
    }

    let options: AxiosRequestOptions = {
        method,
        withCredentials: true,
        headers,
    }

    if (!data) return options

    if (method === 'get') {
        options.params = data
    } else {
        options.data = data
    }

    return options
}

const csrfToken = () => {
    let xsrfToken: any

    xsrfToken = document.cookie.match('(^|; )XSRF-TOKEN=([^;]*)') || document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content
    xsrfToken = xsrfToken[2]
    xsrfToken = decodeURIComponent(xsrfToken)

    return xsrfToken
}
