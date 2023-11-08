(function($, Drupal) {

    $('#btn-wso2-test').on('click', (e) => {
        sendToApiManager()
    });


    function sendToApiManager(preferenze) {
      
      let rootEndPoint = window.location.protocol + "//" +  window.location.hostname 

      $('#btn-wso2-test').addClass('disabled');
      $('#wso2-response').val('Invio richiesta....');

      
      return new Promise(function(resolve, reject) {
          
          $.ajax({
              // definisco il tipo della chiamata
              type: "GET",
              // specifico la URL della risorsa da contattare
              url: rootEndPoint + "/wso2/send",
              // definisco il formato della risposta
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              // passo dei dati alla risorsa remota
              // imposto un'azione per il caso di successo
              success: function(risposta){
                  console.log(risposta);
                  $('#btn-wso2-test').removeClass('disabled');
                  $('#wso2-response').val(JSON.stringify(risposta));
                  resolve(risposta);
              },
              // ed una per il caso di fallimento
              error: function(error){
                  $('#btn-wso2-test').removeClass('disabled');
                  $('#wso2-response').val(error);
              }
          });
      });
  }

})(jQuery, Drupal);