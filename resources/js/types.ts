export interface TranslationsConfigProps {
  translations: object
  locale: string
}

export interface RoutesConfigProps {
  routes: object
}

export interface ModelsConfigProps {
  shared: {
    methods: object
  }
  models: object
}

export interface ConfigProps {
  translations: object, //TranslationsConfigProps
  routes: object, //RoutesConfigProps
  models: object, //ModelsConfigProps
  notifications: object,
  actions: object,
  jobs: object
}

export interface AxiosRequestOptions {
  method: string
  params?: object
  data?: object
  headers: object
  withCredentials: boolean
}

export interface ActionRequestConfig {
  method?: string
  data?: object
  headers?: object
  ignoreCache?: boolean
  allowStaleCache?: boolean
}

export interface ActionRequest {
  url: string
  data?: object
  config?: ActionRequestConfig
}

export interface ActionResponse {
  data: any
}

export interface ActionQueueItem {
  key: string
  request: ActionRequest
  resolve: (value: unknown) => void
  reject: (reason?: any) => void
}
