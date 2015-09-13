# asynchronous-server-tasks
A wrapper to implement asynchronous server taks in your PHP project


remember to run first ```composer dumpautoload``` in order to generate the appropriate autoload files


To run the example:

from the root of the project in two different terminal type this:

terminal1> php55 -S localhost:40000 -t example-client 

terminal2> php55 -S localhost:40001 -t example-server

now, go to your brower and write http://localhost:4000 and you'll see a simple page with a button to start a new task
and an area to see messages.
