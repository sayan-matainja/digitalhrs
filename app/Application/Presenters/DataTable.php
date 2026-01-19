<?php

/**
 * Created by PhpStorm.
 * User: sandeep
 * Date: 6/14/22
 * Time: 4:59 AM
 */

namespace App\Application\Presenters;

class DataTable
{

    public static function createHtmlAction($action_name = '', $action_url = null, $action_title, $action_icon = 'eye', $action_class = 'default', $target = '')
    {
        return '<a href="' . (is_null($action_url) ? "javascript:void(0)" : $action_url) . '" target="' . $target . '">
                    <button data-placement="left" data-tooltip="true" title="' . $action_title . '"   class="btn btn-xs btn-' . $action_class . '">
                    <span class="fa fa-' . $action_icon . '"></span>
                    ' . $action_name . '
                </button>
                </a>';
    }


    public static function makeDeleteAction($action_name = '', $action_url = null, $model, $type, $item_identifier)
    {
        $delete_action = '<button data-placement="left" data-tooltip="true" title="' . $action_name . '"  class="btn btn-xs btn-danger" data-toggle="modal" data-target="#delete' . $model->id . '">
                         <span class="fa fa-trash"></span>
                           ' . $action_name . '
                         </button>
                ' . self::deleteModal($model, '' . $action_url, $type, $item_identifier) . '';
        return $delete_action;
    }


    private static function deleteModal($item, $route_name, $type, $item_identifier)
    {
        return '<div class="modal fade" id="delete' . $item->id . '" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
               <div class="modal-dialog">
                  <div class="modal-content">
                     <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                       <h4 class="modal-title custom_align" id="Heading">Do You Want To Delete ' . $type . ' : ' . ucwords($item_identifier) . ' ?</h4>
                </div>
                 <div class="modal-body">
                   <div id ="alert_delete" style="font-size:15px;font-weight:bolder;text-align:center;" class="text-red"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;The Deleted ' . $type . '  cannot be restored .</div>
               </div>

               <div class="modal-footer ">
                  <form role="form" action="' . $route_name . '" method="post">
                    ' . csrf_field() . '
                    <input type="hidden" name="_method" value="DELETE">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Delete</button>
                   </form>
                 </div>
                  </div>
               </div>
              </div>';

    }


    public static function createDeleteAction($btnText = '', $actionUrl = null, $uniqueKey, $itemType, $itemName)
    {
        $delete_action = '<button data-placement="left" data-tooltip="true" title="' . $btnText . '"  class="btn btn-xs btn-danger" data-toggle="modal" data-target="#delete' . $uniqueKey . '">
                         <span class="fa fa-trash"></span>
                           ' . $btnText . '
                         </button>
                ' . self::newDeleteModal($actionUrl, $uniqueKey, $itemType, $itemName) . '';
        return $delete_action;
    }

    private static function newDeleteModal($actionUrl, $uniqueKey, $itemType, $itemName)
    {
        return '<div class="modal fade" id="delete' . $uniqueKey . '" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
               <div class="modal-dialog">
                  <div class="modal-content">
                     <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                       <h4 class="modal-title custom_align" id="Heading">Do You Want To Delete ' . $itemType . ' : ' . ucwords($itemName) . ' ?</h4>
                </div>
                 <div class="modal-body">
                   <div id ="alert_delete" style="font-size:15px;font-weight:bolder;text-align:center;" class="text-red"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;The Deleted ' . $itemType . '  cannot be restored .</div>
               </div>

               <div class="modal-footer ">
                  <form role="form" action="' . $actionUrl . '" method="post">
                    ' . csrf_field() . '
                    <input type="hidden" name="_method" value="DELETE">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Delete</button>
                   </form>
                 </div>
                  </div>
               </div>
              </div>';

    }
}

