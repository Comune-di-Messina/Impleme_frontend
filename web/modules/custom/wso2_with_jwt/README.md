# Introduzione 
Permette di dialogare con un API Manager, in possesso di **client_id** e **client_secret**, gestirà il recupero dell'**access_token** e il passaggio del **JWT** ottenuto dal modulo [OpenID Connect](https://www.drupal.org/docs/8/modules/openid-connect).

Per maggiori informazioni leggere il documento **Integrazione WSO2-Drupal8_v1.docx**.

# Getting Started
1.  Andare nel menù di Drupal > Configuration > WebServices > WSO2 WITH JWT, ed impostare nella form il base url dell' Api Manager, e i relativi client_id e client_secret
2.	Creare una classe estensione di **Wso2Connection** (vedi classe **PizzaShackConnection**),
2.	Utilizzare la classe di tipo Wso2Connection all'interno di un Controller se sia necessario esporlo come servizio.