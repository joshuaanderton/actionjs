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
          { url, config } = request,
          instance = axios.create(),
          options = getRequestOptions(config || {})

    const axiosRequest = instance({
        url,
        ...options,
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
        url: '/api/actionjs/batch',
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

const getRequestOptions = ({ method = 'get', data, headers = {} }: ActionRequestConfig): AxiosRequestOptions => {

    let options: AxiosRequestOptions = {
        method,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': csrfToken(),
            ...headers
        },
        withCredentials: true,
    }

    if (!data) return options

    if (method === 'get') {
        options.params = data
    } else {
        options.data = data
        headers['Content-Type'] = 'application/json'
    }

    return options
}

const csrfToken = () => {
    if (typeof document === 'undefined') {
        return
    }

    let xsrfToken

    xsrfToken = document.cookie.match('(^|; )XSRF-TOKEN=([^;]*)') || 0
    xsrfToken = xsrfToken[2]
    xsrfToken = decodeURIComponent(xsrfToken)

    return xsrfToken
}
