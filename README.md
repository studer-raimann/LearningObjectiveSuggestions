# LearningObjectiveSuggestions

ILIAS Cronjob Plugin which calculates scores for learning objectives of a course. 
Based on the scores, learning objectives are suggested to a user for further learning.

## Installation
Start at your ILIAS root directory
```
mkdir -p Customizing/global/plugins/Services/Cron/CronHook  
cd Customizing/global/plugins/Services/Cron/CronHook
git clone https://git.studer-raimann.ch/ILIAS-Kunde-DHBW-Karlsruhe/LearningObjectiveSuggestions.git
```
As an ILIAS administrator, go to "Administration -> Plugins" and install/activate the plugin.

## Configuration
The plugin MUST be configured accordingly before the cronjobs run correctly. Make sure that the
configuration is correct prior to activate the cron jobs ("Administration -> General -> Cron Jobs")

## Cron Jobs


