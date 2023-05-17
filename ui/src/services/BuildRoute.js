

export const BuildRoute = {

  export (routes) {
    // console.log(routes)
    const data = []
    routes.map(r => {
      const route = { name: r.name, path: r.path }
      if (r.name) data.push(route)
    })

    data.sort((a, b) => {
      const nameA = (a.name || '').toUpperCase() // Convert undefined to an empty string
      const nameB = (b.name || '').toUpperCase()
      if (nameA < nameB) return -1
      if (nameA > nameB) return 1
      return 0
    })

    const jsonContent = JSON.stringify(data, null, 2) // null dan 2 digunakan untuk indentasi yang lebih mudah dibaca
    const blob = new Blob([jsonContent], { type: 'application/json' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'ui_route.json'
    link.click()
    window.URL.revokeObjectURL(url)
  }

}
