# ActionJS
### Run server-side Laravel actions using javascript client-side

`composer require joshuaanderton/actionjs`

```
import { defineConfig } from 'vite'
import actionjs from '../vendor/joshuaanderton/actionjs'

export default defineConfig({
  plugins: [
    laravel(...),
    actionjs()
  ]
})
```

## Server Side
```
<?php

namespace App\Actionjs;

use App\Models\Product;

class SearchProducts
{
    public function __invoke()
    {
        $request->validate(['term' => 'required|string']);

        $products = Product::where('title', $request->term)->get();

        return response()->json(['products' => $products], 200);
    }
}
```

### Client Side

```
import actionjs from '@actionjs'

actionjs.searchProducts({ term: 'star wars' }).then(products => console.log(products))
```
