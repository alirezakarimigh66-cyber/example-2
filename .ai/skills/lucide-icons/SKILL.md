---
name: lucide-icons
description: a guide to use lucide icons
---

# Lucide Icons
Every time you want to use an icon you should use one of lucide icons.

## Where to Find Icons
You can search the needed Icon in the `vendor/mallardduck/blade-lucide-icons/resources/svg/icons` path.

## How to Use Icons
You should add lucide icons using blade like following:
```bladehtml
<x-lucide-{icon-name} />
```
For example, to use the activity icon you should add the following code:
```bladehtml
<x-lucide-activity />
```

## How to Modify Icons
You can use tailwind css to modify the icons like following:
```bladehtml
<x-lucide-album class="w-6 h-6 text-gray-500"/>
```
