name: Lint
on: [ push ]
jobs:
  lint:
    name: Lint code
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v1
      - name: "Lint code"
        uses: aglipanci/laravel-pint-action@2.0.0
        with:
          preset: laravel
          verboseMode: true
          testMode: true
          pintVersion: 1.8.0
          onlyDirty: true
