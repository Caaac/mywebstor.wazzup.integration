
Object.defineProperty(Object.prototype, 'deepCopy', {
  enumerable: false,
  value: function () {
    return JSON.parse(JSON.stringify(this))
  }
})

Object.defineProperty(Object.prototype, 'timeToStr', {
  enumerable: false,
  value: function () {
    return this ? (this.getDate() < 10 ? '0' : '') + this.getDate() + '.' + (this.getMonth() < 9 ? '0' : '') + (+this.getMonth() + 1) + '.' + this.getFullYear() : '';
  }
})