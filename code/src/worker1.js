

importScripts('util2.js'); 


/*

  Do I need to terminate / close / delete a web worker when i'm done with it?

    Send it a message telling it to clean up, and have the worker respond to that message 
    by unregistering all event handlers. This lets the worker exit gracefully.
    
    Use Worker#terminate, which terminates the worker immediately without giving it any 
    chance to finish what it's doing.


*/

onconnect = function(e) {
}


onmessage = function(e) {
  postMessage( 'Résultat=' + (e.data[0] * e.data[1]) );
}


