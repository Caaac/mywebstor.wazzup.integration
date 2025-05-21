Object.defineProperty(Object.prototype, 'deepCopy', {
  enumerable: false,
  value: function () {
    return JSON.parse(JSON.stringify(this))
  }
})