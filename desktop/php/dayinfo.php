<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'dayinfo');
$eqLogics = eqLogic::byType('dayinfo');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4" id="hidCol" style="display: none;">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%" /></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-xs-12 eqLogicThumbnailDisplay" id="listCol">

    <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoSecondary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br />
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br />
        <span>{{Configuration}}</span>
      </div>
    </div>

    <legend><i class="fas fa-home" id="butCol"></i> {{Mes Equipements}}</legend>
    <div class="input-group" style="margin:5px;">
      <input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
      <div class="input-group-btn">
        <a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i>
        </a><a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
      </div>
    </div>
    <div class="eqLogicThumbnailContainer">
      <?php
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
        echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
        echo '<img src="' . $eqLogic->getImage() . '" style="max-height: 95px"/>';
        echo "<br>";
        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>


  <div class="col-xs-12 eqLogic" style="display: none;">
    <div class="input-group pull-right" style="display:inline-flex">
      <span class="input-group-btn">
        <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
        </a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
        </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
        </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i><span class="hidden-xs"> {{Supprimer}}</span>
        </a>
      </span>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br />
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom des infos du jour}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement dayinfo}}" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Objet parent}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                  $options = '';
                  foreach ((jeeObject::buildTree(null, false)) as $object) {
                    $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                  }
                  echo $options;
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-8">
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
              <label class="col-sm-3 control-label"></label>
              <div class="col-sm-3">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
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
                  $none = 0;
                  if (class_exists('geotravCmd')) {
                    foreach (eqLogic::byType('geotrav') as $geoloc) {
                      if ($geoloc->getConfiguration('type') == 'location') {
                        $none = 1;
                        echo '<option value="' . $geoloc->getId() . '">' . $geoloc->getName() . '</option>';
                      }
                    }
                  }
                  if ((config::byKey('info::latitude') != '') && (config::byKey('info::longitude') != '') && (config::byKey('info::postalCode') != '') && (config::byKey('info::stateCode') != '')) {
                    echo '<option value="jeedom">Configuration Jeedom</option>';
                    $none = 1;
                  }
                  if ($none == 0) {
                    echo '<option value="">Pas de localisation disponible</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <div id="calendar" class="form-group" style="display:none">
              <label class="col-sm-3 control-label">{{Calendrier ICS}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="calendar" placeholder="{{Emplacement du calendrier}}" />
              </div>
            </div>

          </fieldset>
        </form>
      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">
        <div class="table-responsive">
          <table id="table_cmd" class="table table-bordered table-condensed">
            <thead>
              <tr>
                <th style="width: 50px;">ID</th>
                <th>{{Nom}}</th>
                <th style="width: 250px;">{{Paramètres}}</th>
                <th style="width: 150px;">{{Actions}}</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
        <script>
          $("#selCountry").change(function() {
            //alert($(.li_eqLogic.active).getAttribute["data-eqLogic_id"]);
            nodeId = $('.li_eqLogic.active').attr("data-eqLogic_id");
            $.ajax({ // fonction permettant de faire de l'ajax
              type: "POST", // méthode de transmission des données au fichier php
              url: "plugins/dayinfo/core/ajax/dayinfo.ajax.php", // url du fichier php
              data: {
                action: "getZones",
                id: $(this).val(),
              },
              dataType: 'json',
              global: false,
              error: function(request, status, error) {
                handleAjaxError(request, status, error);
              },
              success: function(data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                  });
                  return;
                }
                var options = '';
                for (var i in data.result) {
                  var value = data.result[i]['value'];
                  options += '<option value="' + i + '">' + value + '</option>';
                }
                $("#selZones").html(options);
                modifyWithoutSave = false;
                $.ajax({ // fonction permettant de faire de l'ajax
                  type: "POST", // méthode de transmission des données au fichier php
                  url: "plugins/dayinfo/core/ajax/dayinfo.ajax.php", // url du fichier php
                  data: {
                    action: "confZone",
                    id: nodeId,
                  },
                  dataType: 'json',
                  global: false,
                  error: function(request, status, error) {
                    handleAjaxError(request, status, error);
                  },
                  success: function(data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                      $('#div_alert').showAlert({
                        message: data.result,
                        level: 'danger'
                      });
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