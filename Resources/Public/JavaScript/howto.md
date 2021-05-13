Short description:

```
cd public/typo3conf/ext/web_vitals_tracker/Resources/Public/JavaScript
cd Resources/Public/JavaScript
curl -fsSL https://deno.land/x/install/install.sh | sh
yarn add esbuild
/home/application/.deno/bin/deno bundle performance.ts > performance.js
yarn esbuild performance.js --minify --outfile=performance.min.js
```
