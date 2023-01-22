

importScripts('util2.js'); 

onmessage = function(e) {
  console.log('Message reçu depuis le script principal : data[0]='+e.data[0]);
  var workerResult = 'Résultat : ' + (e.data[0] * e.data[1]);
  console.log('Envoi du message de retour au script principal');
  postMessage(workerResult);
}


