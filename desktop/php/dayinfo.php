<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'dayinfo');
$eqLogics = eqLogic::byType('dayinfo');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fas fa-plus-circle"></i> {{Ajouter un équipement}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Mes dayinfo}}
    </legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <center>
          <i class="fas fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
      </div>
      <?php
      $dir = dirname(__FILE__) . '/../../doc/images/';
      $files = scandir($dir);
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        $test = $eqLogic->getConfiguration('type') . '.png';
        if (in_array($test, $files)) {
          $path = $eqLogic->getConfiguration('type');
        } else {
          $path = 'dayinfo_icon';
        }
        echo '<img src="plugins/dayinfo/doc/images/' . $path . '.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>


  <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
    <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
    <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom des infos du jour}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement dayinfo}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                  foreach (object::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-3">
                <?php
                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                  echo '<label class="checkbox-inline">';
                  echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                  echo '</label>';
                }
                ?>

              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" ></label>
              <div class="col-sm-3">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Type d'informations}}</label>
              <div class="col-sm-3">
                <select id="typeEq" class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="type">
                  <option value="bankdays">Jours fériés</option>
                  <option value="holidays">Vacances scolaires</option>
                  <option value="moon">Lune</option>
                  <option value="various">Divers</option>
                  <option value="global">Global</option>
                  <option value="calendar">Calendrier iCal</option>
                </select>
              </div>
            </div>

            <div id="cmdgeoloc" class="form-group" style="display:none">
              <label class="col-sm-3 control-label">{{Localisation à utiliser}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="geoloc">
                  <?php
                  if (class_exists('geotravCmd')) {
                    foreach (eqLogic::byType('geotrav') as $geoloc) {
                      if ($geoloc->getConfiguration('type') == 'location') {
                        echo '<option value="' . $geoloc->getId() . '">' . $geoloc->getName() . '</option>';
                      }
                    }
                  } else {
                    echo '<option value="">Pas de localisation disponible</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <div id="calendar" class="form-group" style="display:none">
              <label class="col-sm-3 control-label">{{Calendrier ICS}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="calendar" placeholder="{{Emplacement du calendrier}}"/>
              </div>
            </div>

          </fieldset>
        </form>
      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">
        <br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th style="width: 250px;">{{Nom}}</th>
              <th style="width: 150px;">{{Paramètres}}</th>
              <th style="width: 50px;"></th>
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>

        <script>

        $( "#selCountry" ).change(function() {
          //alert($(.li_eqLogic.active).getAttribute["data-eqLogic_id"]);
          nodeId = $('.li_eqLogic.active').attr( "data-eqLogic_id" );
          $.ajax({// fonction permettant de faire de l'ajax
          type: "POST", // méthode de transmission des données au fichier php
          url: "plugins/dayinfo/core/ajax/dayinfo.ajax.php", // url du fichier php
          data: {
            action: "getZones",
            id: $( this ).val(),
          },
          dataType: 'json',
          global: false,
          error: function (request, status, error) {
            handleAjaxError(request, status, error);
          },
          success: function (data) { // si l'appel a bien fonctionné
          if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
          }
          var options = '';
          for (var i in data.result) {
            var value = data.result[i]['value'];
            options += '<option value="'+i+'">'+value+'</option>';
          }
          $("#selZones").html(options);
          modifyWithoutSave = false;
          $.ajax({// fonction permettant de faire de l'ajax
          type: "POST", // méthode de transmission des données au fichier php
          url: "plugins/dayinfo/core/ajax/dayinfo.ajax.php", // url du fichier php
          data: {
            action: "confZone",
            id: nodeId,
          },
          dataType: 'json',
          global: false,
          error: function (request, status, error) {
            handleAjaxError(request, status, error);
          },
          success: function (data) { // si l'appel a bien fonctionné
          if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
          }
          $("#selZones").value(data.result);
          modifyWithoutSave = false;
        }
      });
    }
  });
});

</script>
</div>
</div>
</div>
</div>

<?php include_file('desktop', 'dayinfo', 'js', 'dayinfo'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
