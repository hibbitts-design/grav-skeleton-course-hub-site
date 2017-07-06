---
title: Home
body_classes: 'header-image fullwidth'
child_type: item
content:
    items: '@self.children'
    limit: 0
    order:
        by: date
        dir: desc
    pagination: '1'
hidegitrepoeditlink: false
blog_url: home
sitemap:
    changefreq: monthly
    priority: 1.03
modular_content:
    items: '@self.modular'
    order:
        dir: desc
feed:
    description: 'Course Hub Description'
    limit: 10
pagination: false
---
