import translations from '@actionjs/../../dist/config/translations'
import routes from '@actionjs/../../dist/config/routes'
import models from '@actionjs/../../dist/config/models'
import notifications from '@actionjs/../../dist/config/notifications'
import actions from '@actionjs/../../dist/config/actions'
import jobs from '@actionjs/../../dist/config/jobs'
import { ConfigProps } from '../types'

const config: ConfigProps = {
  translations,
  routes,
  models,
  notifications,
  actions,
  jobs
}

export default config
