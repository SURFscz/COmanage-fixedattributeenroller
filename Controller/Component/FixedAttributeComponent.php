<?php
/**
 * FixedAttributeEnroller FixedAttributeComponent
 *
 * Author licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.surfnet.nl
 * @package       COmanage-fixedattributeenroller
 * @since         2018-10-01
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 *
 * This is a convenience component to allow other enroller plugins to interface on the
 * same behaviour regarding return URL components and attribute-extraction
 */

class FixedAttributeComponent extends Component {
  /** 
   * Retrieve a given attribute against the OrgIdentity
   *
   * @param Array $oi         OrgIdentity artifact
   * @param String $attribute Attribute to check against
   */
  public function getAttribute($oi, $attribute) {

    // we support attribute:type configurations to allow for testing a specific email address 
    $values = explode(':',$attribute);
    $type=null;
    if(sizeof($values) > 1) {
      $attribute=$values[0];
      $type=$values[1];
    }

    $valuefound=null;
    // if the attribute is a model name, check for the related fields
    if(isset($oi[$attribute])) {

      // To distinguish between a non-existing attribute and a supported attribute
      // for which we do not have a value, we set $valuefound to the empty array at this point.
      $valuefound=array();

      // loop over all recovered models of this attribute (ie: all email addresses)
      $models = $oi[$attribute];
      if($attribute == "PrimaryName") {
        // PrimaryName is a hasOne association
        $models = array($models);
      }
      foreach($models as $model) {
        switch($attribute) {
        case 'TelephoneNumber':
          if($type === null || $type == $model['type']) {
            $valuefound[] = formatTelephone($model);
          }
          break;
        case 'Address':
          if(in_array($type, array('street','state','postal_code','room','locality','country'))) {
            if($model[$type] !== null) {
              $valuefound[] = $model[$type];
            }
          }
          break;
        case 'PrimaryName':
          $valuefound[] = generateCn($model);
          break;
        case 'Name':
          if(  ($type !== null && $type == $model['type']) 
            || ($type === null)) {
            $valuefound[] = generateCn($model);
          }
          break;
        case 'Identifier':
          if($type === null || $type == $model['type']) {
            $valuefound[] = $model['identifier'];
          }
          break;
        case 'EmailAddress':
          if($type === null || $type == $model['type']) {
            $valuefound[] = $model['mail'];
          }
          break;
        case 'Url':
          if($type === null || $type == $model['type']) {
            $valuefound[] = $model['url'];
          }
          break;
        }
      }
    }
    return $valuefound;
  }


  /** 
   * Match a given attribute against the OrgIdentity
   *
   * Instead of matching the attribute itself, we match the sha256 encoded value
   *
   * @param Array $oi         OrgIdentity artifact
   * @param String $attribute Attribute to check against
   * @param String $value     Attribute value to match
   */
  public function matchAttribute($oi, $attribute, $value) {
    $valuefound = $this->getAttribute($oi,$attribute);

    // if we do not support this attribute, we skip validation (valuefound is NULL)
    // However, if we do support it, but we have no value, or the value is a mismatch, 
    // throw an error
    
    if($valuefound === null || !is_array($valuefound)) {
      return true;
    }
    
    // convert all array values to hashes. We could have done that in the getAttribute method,
    // but that would reduce its applicability and make debugging cumbersome
    $hashes = array();
    foreach($valuefound as $val) {
      $hashes[] = hash('sha256',$val);
    }
    if(!in_array($value,$hashes))  {
      throw new RuntimeException("Not Authorized");
    }
    return true;
  }

  /** 
   * Parse a return parameter into a list of values
   *
   * Allow for a complete URL specification including a QUERY part, or only the QUERY part
   *
   * @param String $url Return url parameter
   */
   public function parseUrl($url)
   {
     $values = parse_url($url, PHP_URL_QUERY);
     if($values === FALSE || empty($values)) {
       // assume the entire url is a key-value list
       $values = $url;
     }

     $returnvalues=array();
     if(!empty($values)) {
       foreach(explode('&',$values) as $chunk) {
         $keyvalue = explode('=',$chunk);
         if(isset($keyvalue) && is_array($keyvalue) && sizeof($keyvalue) == 2) {
           $returnvalues[ urldecode($keyvalue[0]) ] = urldecode($keyvalue[1]);
         }
       }
     }
     return $returnvalues;
   }
}
