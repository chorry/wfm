# WFM - web file manager

## Introduction


## Requirements
PHP 5.3+ (although it can be downgraded to 5.0 with mininum efforts. But better not to do it :)

## Installation
* Unpack
* Edit data/configs/config.php
* Done!

## Limitations
	Editor: treats all files as cp-1251 encoded.

## How to use

## Groups
Each user can belong to one or more groups with different access rules.
If there are two identical rules from different groups, allow rule takes precedence over denying rule (dats not implemented yet).

## Rules
All rules are located in data/configs/groups (default).
List of rule actions and filters:
	File: create, rename, delete, move, read, write.
	Directory: create, rename, delete, move, list.
	Editor filter: disallowed content, file extensions
	Filters: filter by extension

For now, most of rules are plain regexp patterns. When you're writing a filesystem rule, best to use absolute paths to avoid unexpected matches.

## Credits
Alloy Url Router - http://alloyframework.org/
Bootstrap - http://twitter.github.com/bootstrap/
Twig template engine - http://symfony.com