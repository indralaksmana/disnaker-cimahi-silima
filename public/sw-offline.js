workbox.skipWaiting()
workbox.clientsClaim()

// fonts
workbox.routing.registerRoute(
    new RegExp('https://fonts.*'),
    workbox.strategies.cacheFirst({
        cacheName: 'fonts',
        plugins: [
            new workbox.cacheableResponse.Plugin({
                statuses: [0, 200]
            })
        ]
    })
)

// google stuff
workbox.routing.registerRoute(
    new RegExp('.*(?:googleapis|gstatic).com.*$'),
    workbox.strategies.networkFirst({
        cacheName: 'google'
    })
)

// static
workbox.routing.registerRoute(
    new RegExp('.(?:js|css|ico)$'),
    workbox.strategies.networkFirst({
        cacheName: 'static'
    }),
)

// images
workbox.routing.registerRoute(
    new RegExp('.(?:jpg|png|gif|svg)$'),
    workbox.strategies.cacheFirst({
        cacheName: 'images',
        plugins: [
            new workbox.expiration.Plugin({
                maxEntries: 20,
                purgeOnQuotaError: true,
            })
        ]
    })
)

// pre-cache pages
workbox.precaching.precacheAndRoute([
    {
       url: '/offline',
       revision: Date.now()
    }
  ])

  /**
  * save pages to cache on visit & serve when offline
  * or if not cached then serve the "offline view"
  */
  const customHandler = async (args) => {
      try {
          return await workbox.strategies.networkFirst({
              cacheName: 'pages',
              plugins: [
                  new workbox.expiration.Plugin({
                      maxEntries: 20,
                      purgeOnQuotaError: true
                  })
              ]
          }).handle(args) || caches.match('offline')
      } catch (error) {
          return caches.match('offline')
      }
  }

  const navigationRoute = new workbox.routing.NavigationRoute(customHandler, {
      // dont cache this urls
      blacklist: [
          new RegExp('/login'),
          new RegExp('/login-modal')
      ]
  })

  workbox.routing.registerRoute(navigationRoute)
