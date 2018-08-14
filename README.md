# Terminus Stooges Plugin
[![Terminus v1.x Compatible](https://img.shields.io/badge/terminus-v1.x-green.svg)](https://github.com/terminus-plugin-project/terminus-stooges-plugin/tree/1.x)

*Note that this plugin only supports WordPress*

Terminus plugin to search and destroy Platform/Vanity domains from an environment.
This is an automated way of using WP CLI's search and replace functionality to ensure copies
of a Pantheon URL (pantheonsite.io) are removed from content in the database.

## Use Case
When taking your WordPress site live, you are often left with links pointing to live-sitename.pantheonsite.io, 
or other links from dev, test, multidev, or vanity domain environments.  While each can be cleaned up individually,
this is a time consuming process requiring multiple commands to be run.  This plugin aims to simplify this process.

## Examples
### Replace platform and vanity domains from the dev environment
`$ terminus site:stooges companysite-33.dev`

### Replace all platform and vanity domains from the live environment, regardless of source environment
`$ terminus site:stooges:sparky companysite-33`

## Installation
To install this plugin place it in `~/.terminus/plugins/`.

On Mac OS/Linux:
```
mkdir -p ~/.terminus/plugins
composer create-project -d ~/.terminus/plugins terminus-plugin-project/terminus-stooges-plugin:~1
```

## Help
Run `terminus help site:stooges` for help.
