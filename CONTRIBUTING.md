# Contribuir al proyecto

Las formas de contribuir al proyecto de sistema de gestión de postgrado son las siguientes:

* code: contribute your expertise in an area by helping us expand OSEM
* ideas: participate in an issues thread or start your own to have your voice heard.
* copy editing: fix typos, clarify language, and generally improve the quality of the content of OSEM

# How to contribute
* Prerequisites: familiarity with [GitHub Pull Requests](https://help.github.com/articles/using-pull-requests) and issues.
* Fork the repository and make a pull-request with your changes
  * Make sure that the test suite passes (we have [travis](https://travis-ci.org/openSUSE/osem) enabled) before you request a pull and that you comply to our ruby styleguide.
  * Please make sure to mind what travis tells you! :-)
  * Please increase code coverage by your pull request (coveralls or simplecov locally will give you insight)

* One of the OSEM maintainers will review your pull-request
  * If you are already a contributor and you get a positive review, you can merge your pull-request yourself
  * If you are not a contributor already please request a merge via the pull-request comments
* Run rubocop locally to check for any ruby style offenses
  * `bundle exec rubocop`

# Conduct
OSEM is part of the openSUSE project. We follow all the [openSUSE Guiding
Principles!](http://en.opensuse.org/openSUSE:Guiding_principles) If you think
someone doesn't do that, please let us know at opensuse-web@opensuse.org.

# Communication
GitHub issues are the primary way for communicating about specific proposed
changes to this project. If you have other questions feel free to subscribe to
the [opensuse-web@opensuse.org](http://lists.opensuse.org/opensuse-web/)
mailinglist, all OSEM contributors are on that list! Additionally you can use #osem channel
on freenode IRC.
