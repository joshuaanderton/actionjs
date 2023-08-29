import { UserConfig, searchForWorkspaceRoot } from 'vite'
import { cwd } from 'node:process'
import path from 'path'

interface Props {

}

export default (options: Props) => ({

  name: '@actionjs',

  config: (config: UserConfig, { mode, command }: { mode: string, command: string }) => {

    if (!['build', 'serve'].includes(command)) {
      return config
    }

    const basePath = searchForWorkspaceRoot(cwd()),
          packagePath = path.resolve(__dirname)

    config.resolve = {
      alias: {
        ...(config.resolve?.alias || {}),
        '@tightenco/ziggy': `${basePath}/vendor/tightenco/ziggy/src/js`,
        '@actionjs': `${packagePath}/resources/js`,
        '@pckg': path.resolve('./node_modules'),
      }
    }

    // Allow importing from package directory in ./vendor (or ../packages when in development)
    config.server = {
      fs: {
        allow: [
          ...(config.server?.fs?.allow || []),
          path.relative(basePath, packagePath),
          basePath
        ]
      }
    }

    // Preserve symlinks (e.g. when using ../packages folder in development)
    if (mode === 'development') {
      config.preserveSymlinks = true
    }

    return config
  }
})
