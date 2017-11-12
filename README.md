Participation credit
====================

[![Build Status](https://travis-ci.org/mudrd8mz/moodle-workshopeval_credit.svg?branch=master)](https://travis-ci.org/mudrd8mz/moodle-workshopeval_credit)

Simple grading evaluation method for Moodle Workshop module that gives credit
to peer-reviewers just for their participation in the activity without
comparing their assessment with the others.

There are three modes of how grades for assessment are calculated by this method.

* All or nothing - The reviewer must assess all allocated submissions in order
  to obtain the maximum grade; otherwise they receive a grade of zero.
* Proportional - The grade obtained is proportional to the number of
  assessments. If all allocated submissions are assessed, the reviewer will
  obtain the maximum grade; if half of the allocated submissions are assessed,
  the reviewer will obtain 50% of the maximum grade.
* At least one - The reviewer must assess at least one allocated submission in
  order to obtain the maximum grade.


Installation
------------

Unzip the plugin into the folder mod/workshop/eval/credit/ on your Moodle site
and visit the main administration page.


Usage
-----

When the workshop activity is in the grading evaluation phase, choose the evaluation
method "Participation credit". Then select the required evaluation mode and click
"Re-calculate grades".
