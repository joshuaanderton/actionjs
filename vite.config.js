import path from 'path'
import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    lib: {
      entry: path.resolve(__dirname, 'resources/js/index.ts'),
      name: 'ActionJS',
      fileName: 'actionjs',
    },
    /*
    rollupOptions: {
      // make sure to externalize deps that shouldn't be bundled
      // into your library
      external: ['vue'],
      output: {
        // Provide global variables to use in the UMD build
        // for externalized deps
        globals: {
          vue: 'Vue',
        },
      },
    },
    */
  },
})
