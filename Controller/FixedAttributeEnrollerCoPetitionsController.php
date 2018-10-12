<?php

/* Author licenses this file to you under the Apache License, Version 2.0
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
 *
 * Plugin used to match attributes provided on the initial enrollment URI with values determined on the OrgIdentity.
 * For any such attribute, an identifier is created (if the attribute itself is not an identifier) for export
 * using LDAP.
 *
 * Currently, the plugin uses the 'return' parameter to store the query values. To allow a 'combined' use, the
 * return parameter is interpreted as a full URL (from which the query parameters are stripped) if possible.
 * If the return parameter is not a full URL, it is assumed to be a urlencoded key-value list
 *
 * To allow for properly matching and creating identifiers, the parameters should be created as follows:
 * <attribute name> = sha256(expected value)
 * <attribute name>:<type name> = sha256(expected value)
 * <attribute name>:<type name>:<identifier type name> = sha256(expected value)
 *
 * Examples:
 * email=d83726dfe5d201627b5f4eb6b83bbcb3055dfab84820e072906dc04c8604d9cd  (my@example.org)
 * email:office=d83726dfe5d201627b5f4eb6b83bbcb3055dfab84820e072906dc04c8604d9cd
 * email:office:badge=d83726dfe5d201627b5f4eb6b83bbcb3055dfab84820e072906dc04c8604d9cd
 *
 * The first line will check any email address for a match. If matched, it creates an identifier of type
 * 'uid' with the email address.
 * The second line checks only email addresses of type 'office'. If matched, it creates an identifier of
 * type 'uid' with the email address.
 * The third line checks only email addresses of type 'office'. If matched, it creates an identifier of
 * type 'badge' with the email address.
 *
 * The scenario is specifically useful when the invitee (a Service Provider) expects the email address 
 * as a recognisable identifier for the newly enrolled user. This plugin checks the incoming email address
 * against the expected value and exports it as an identifier again.
 *
 * Interesting usage;
 * - make enrollment flow (copy form self-signup template)
 * - Strip to bare minimum, no approval, no notification
 * - only allow authenticated users
 * - Mail Confirmation Mode = Automatic
 * - Remove all enrollment attributes
 * - Configure the use of EnvSource or SamlSource OIS plugins
 * - Configure the use of a pipeline from the OIS plugin
 *
 * Expected result:
 * - User needs to authenticate at the start of enrollment
 * - This plugin checks the validity of specific preset attributes and only allows enrollment on a match
 * - OIS plugin creates authoritive OrgIdentity based on SAML assertions
 * - Pipeline creates synchronised COPerson based on OrgIdentity
 * - Email addresses are automatically considered verified, no verification step required
 * - Approval was done through checking the attribute content after OrgIdentity creation, so no approval required
 *
 * This leads to an enrollment situation where the user has no interaction with COmanage itself and only sees the
 * loading of the intermediate screens.
 *
 * Author: Harry Kodden, 2018, harry.kodden@surfnet.nl
 * Author: Michiel Uitdehaag, 2018, michiel.uitdehaag@surfnet.nl
 */

App::uses('CoPetitionsController', 'Controller');

class FixedAttributeEnrollerCoPetitionsController extends CoPetitionsController {
  // Class name, used by Cake
  public $name = "FixedAttributeEnrollerCoPetitions";
  public $uses = array("CoPetition");
  public $components = array('FixedAttributeEnroller.FixedAttribute', 'Flash');
   
  /**
   * Match the OrgIdentity values with the preset attributes. If the match does not succeed, redirect to a 403
   *
   * We first parse the return parameter of the petition to find correct key-value pairs for attributes to check
   *
   * Example usage:
   * https://example.com/registry/email_uid_enroller/email_uid_enroller_co_petitions/start/coef:6/email:af7b6ae6765d8ffd4bb6504e695de2add675d28d0405549c48f0fbff918d00aa
   *
   * This function inspects the initial REQUEST_URI parameters and stores the hashes in a session variable for later retrieval
   *
   * @param Integer $id 
   * @param Array $onFinish URL, in Cake format
   */

  protected function execute_plugin_petitionerAttributes($id, $onFinish) {

    // Get the Petition artifact
    $args = array();
    $args['conditions']['CoPetition.id'] = $id;
    $args['contain'] = array('EnrolleeOrgIdentity' => array(
      'Address',
      'EmailAddress',
      'Identifier',
      'Name',
      'PrimaryName' => array('conditions' => array('PrimaryName.primary_name' => true)),
      'TelephoneNumber',
      'Url'
      )
    );

    $copetition = $this->CoPetition->find('first', $args);

    try {
      $values = $this->FixedAttribute->parseUrl($copetition['CoPetition']['return_url']);

      if(sizeof($values) && is_array($values)) {
        foreach($values as $key=>$value) {
          $this->FixedAttribute->matchAttribute($copetition['EnrolleeOrgIdentity'], $key,$value);
        }
      }
    }
    catch(Exception $e) {
      // We catch and rethrow to be sure we catch all underlying exceptions
      // as well

      // The conclusion is that either the user has been meddling with his URL parameters,
      // or he/she is using an IdP that returns different parameters than were expected in
      // the invitation. In either case, do not accept this enrollment. We set the user to
      // Deleted state.
      $coPersonId = $this->CoPetition->field('enrollee_co_person_id', array('CoPetition.id' => $id));
      if(!empty($coPersonId)) {
        $this->CoPetition->EnrolleeCoPerson->id = $coPersonId;
        $this->CoPetition->EnrolleeCoPerson->saveField('status', StatusEnum::Denied, array('CoPetition.id' => $id));
      }

      // Flash a short error message to the user
      $this->Flash->set(_txt('er.permission'),array('key'=>'error','clear'=>true));

       // redirect to a generic page
      return $this->redirect("/");
    }

    $this->redirect($onFinish);
  }
}
