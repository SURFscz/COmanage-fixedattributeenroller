<?php

//App::uses('Controller', 'Controller');
//App::uses('CakeRequest', 'Network');
//App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('FixedAttributeComponent', 'FixedAttributeEnroller.Controller/Component');

// A fake controller to test against
//class FixedAttributeEnrollerControllerTest extends Controller {
//    public $paginate = null;
//}

class FixedattributeComponentTest extends CakeTestCase {
    public $FixedAttributeComponent = null;
    public $Controller = null;

    public function setUp() {
        parent::setUp();
        // Setup our component and fake test controller
        $Collection = new ComponentCollection();
        $this->FixedAttributeComponent = new FixedAttributeComponent($Collection);
        //$CakeRequest = new CakeRequest();
        //$CakeResponse = new CakeResponse();
        //$this->Controller = new FixedAttributeEnrollerControllerTest($CakeRequest, $CakeResponse);
    }

    public function testParse() {
        $urls=array(
           "param1=asdasd&param2=sddbsd&param3&param4=&param%205%3D=%20with%20a%20space%3C%2D" => 
              '{"param1":"asdasd","param2":"sddbsd","param4":"","param 5=":" with a space<-"}',
           "https://www.domain.com/path/to/return" => 
              '[]',
           "www.domain.com/path/to/return" => 
              '[]',
           "https:///path/to/return" => 
              '[]',
           "https://www.domain.com" => 
              '[]',
           "https://www.domain.com?param1=12" => 
              '{"param1":"12"}',
           "https://www.domain.com?param1=12#anchortext&more&params" => 
              '{"param1":"12"}',
           "https://www.domain.com/with/a/path?param1=12#anchortext&more&params" => 
              '{"param1":"12"}',
           "/with/a/path?param1=12#anchortext&more&params" => 
              '{"param1":"12"}',
           "/with/a/path?param1=12&param2%20b=%3D&param4#anchortext&more&params" => 
              '{"param1":"12","param2 b":"="}',
           "param3=2&param2%20b=%3D&param4#anchortext&more&params" => 
              '{"param3":"2","param2 b":"="}',
        );

        foreach($urls as $key=>$value) {
          $values = json_encode($this->FixedAttributeComponent->parseUrl($key));
          //echo json_encode($key)." returns ".$values."<br/>";

          $this->assertEquals($value, $values);
        }
    }

    public function testGet() {
      // test matching against a know OI model
      $tnums = array(
          array(
            "country_code" => "12",
            "area_code" => "099",
            "number" => "123331231",
            "extension" => "22",
            "type" => "office"
          ),
          array(
            "country_code" => null,
            "area_code" => "23",
            "number" => "221122",
            "extension" => null,
            "type" => "home"
          ),
          array(
            "country_code" => null,
            "area_code" => null,
            "number" => "22",
            "extension" => null,
            "type" => "mobile"
          ),
          array(
            "country_code" => "12",
            "area_code" => "099",
            "number" => "123331231",
            "extension" => null,
            "type" => "office"
          )
        );
      $addresses = array(
        array(
          "street" => "Riverstreet",
          "room" => "1",
          "locality" => "Riverdale",
          "state" => "Riviera",
          "postal_code" => "11 River",
          "country" => "Rivernia",
          "type" => "office"
        ),
        array(
          "street" => null,
          "room" => null,
          "locality" => "Uptown",
          "state" => "Upstate",
          "postal_code" => null,
          "country" => "Upper Volta",
          "type" => "home"
        ),
        array(
          "street" => "12 Hillstreet",
          "room" => null,
          "locality" => null,
          "state" => null,
          "postal_code" => null,
          "country" => "Switserland",
          "type" => "preferred"
        ),
        array(
          "street" => "Riverstreet",
          "room" => null,
          "locality" => "Riverdale",
          "state" => "Riviera",
          "postal_code" => "11 River",
          "country" => "Rivernia",
          "type" => "office"
        )
      );
      $names = array(
          array(
            "honorific"=> null,
            "given" => "Godfried",
            "middle" => null,
            "family" => "Viggo",
            "suffix" => null,
            "type" => "official",
            "primary_name" => false
          ),
          array(
            "honorific"=> "Mr",
            "given" => "Godfried",
            "middle" => null,
            "family" => "Viggo",
            "suffix" => null,
            "type" => "preferred",
            "primary_name" => false
          ),
          array(
            "honorific"=> null,
            "given" => "Godfried",
            "middle" => null,
            "family" => null,
            "suffix" => null,
            "type" => "official",
            "primary_name" => false
          ),
          array(
            "honorific"=> "Dr.",
            "given" => "Godfried",
            "middle" => "of",
            "family" => "Viggo",
            "suffix" => "jr.",
            "type" => "official",
            "primary_name" => false
          ),
        );

      $ids = array(
        array(
          "identifier" => "viggo7",
          "type" => "uid"
        ),
        array(
          "identifier" => "viggo7",
          "type" => "badge"
        ),
        array(
          "identifier" => "viggo8",
          "type" => "uid"
        ),
        array(
          "identifier" => "viggo9",
          "type" => "orcid"
        )
      );

      $emails = array(
          array(
            "mail" => "Godfried.Viggo@example.nl",
            "type" => "official"
          ),
          array(
            "mail" => "Godfried.Viggo@example.nl",
            "type" => "preferred"
          ),
          array(
            "mail" => "Godfried.Viggo@example.de",
            "type" => "official"
          ),
          array(
            "mail" => "Godfried.Viggo@example.dk",
            "type" => "home"
          ),
        );

      $urls = array(
        array(
          "url" => "http://my.url.com",
          "type" => "office",
        ),
        array(
          "url" => "http://my.url.com",
          "type" => "home",
        ),
        array(
          "url" => "http://my.url.com/me",
          "type" => "office",
        ),
        array(
          "url" => "http://my.url.com/you",
          "type" => "preferred",
        )
      );

      $combinations = array(
        array(
          "TelephoneNumber" => array($tnums[0]),
          "Address" => array($addresses[0]),
          "PrimaryName" => $names[0],
          "Name" => array($names[0],$names[1]),
          "Identifier" => array($ids[0]),
          "EmailAddress" => array($emails[0]),
          "Url" => array($urls[0]),
          "expected" => array(
            'TelephoneNumber' => '["+12 099 123331231 x22"]',
            'TelephoneNumber:office' => '["+12 099 123331231 x22"]',
            'TelephoneNumber:home' => '[]',
            'TelephoneNumber:office:more' => '["+12 099 123331231 x22"]',
            'Garbage' => 'null',
            'Address' => '[]',
            'Address:room' => '["1"]',
            'Address:street' =>  '["Riverstreet"]',
            'Address:locality' => '["Riverdale"]',
            'Address:postal_code' =>  '["11 River"]',
            'Address:state' => '["Riviera"]',
            'Address:country' =>  '["Rivernia"]',
            'Address:home' => '[]',
            'PrimaryName' => '["Godfried Viggo"]',
            'PrimaryName:official' => '["Godfried Viggo"]',
            'Name' => '["Godfried Viggo","Godfried Viggo"]',
            'Name:official' => '["Godfried Viggo"]',
            'Name:preferred' => '["Godfried Viggo"]',
            'Name:none' => '[]',
            'Identifier' => '["viggo7"]',
            'Identifier:uid' => '["viggo7"]',
            'Identifier:badge' => '[]',
            'Identifier:none' => '[]',
            'EmailAddress' => '["Godfried.Viggo@example.nl"]',
            'EmailAddress:official' => '["Godfried.Viggo@example.nl"]',
            'EmailAddress:home' => '[]',
            'Url' => '["http:\/\/my.url.com"]',
            'Url:office' => '["http:\/\/my.url.com"]',
            'Url:home' => '[]',
            'Url:preferred' => '[]',
            'Url:none' => '[]' 
          )
        ),
        array(
          "TelephoneNumber" => array($tnums[1], $tnums[1]),
          "Address" => array($addresses[1]),
          "PrimaryName" => $names[1],
          "Name" => array($names[0],$names[1]),
          "Identifier" => array($ids[1]),
          "EmailAddress" => array($emails[1]),
          "Url" => array($urls[1]),
          "expected" => array(
            'TelephoneNumber' => '["23 221122","23 221122"]',
            'TelephoneNumber:office' => '[]',
            'TelephoneNumber:home' => '["23 221122","23 221122"]',
            'TelephoneNumber:office:more' => '[]',
            'Garbage' => 'null',
            'Address' => '[]',
            'Address:room' => '[]',
            'Address:street' => '[]',
            'Address:locality' => '["Uptown"]',
            'Address:postal_code' => '[]',
            'Address:state' =>  '["Upstate"]',
            'Address:country' =>  '["Upper Volta"]',
            'Address:home' => '[]',
            'PrimaryName' => '["Godfried Viggo"]',
            'PrimaryName:official' => '["Godfried Viggo"]',
            'Name' => '["Godfried Viggo","Godfried Viggo"]',
            'Name:official' => '["Godfried Viggo"]',
            'Name:preferred' => '["Godfried Viggo"]',
            'Name:none' => '[]',
            'Identifier' => '["viggo7"]',
            'Identifier:uid' => '[]',
            'Identifier:badge' => '["viggo7"]',
            'Identifier:none' => '[]',
            'EmailAddress' => '["Godfried.Viggo@example.nl"]',
            'EmailAddress:official' => '[]',
            'EmailAddress:preferred' => '["Godfried.Viggo@example.nl"]',
            'EmailAddress:home' => '[]',
            'Url' => '["http:\/\/my.url.com"]',
            'Url:office' => '[]',
            'Url:home' => '["http:\/\/my.url.com"]',
            'Url:preferred' => '[]',
            'Url:none' => '[]' 
          )
        ),
        array(
          "TelephoneNumber" => array($tnums[0], $tnums[1], $tnums[2], $tnums[3]),
          "Address" => array($addresses[0], $addresses[1], $addresses[2], $addresses[3]),
          "PrimaryName" => $names[0],
          "Name" => array($names[0],$names[1], $names[2], $names[3]),
          "Identifier" => array($ids[0], $ids[1],$ids[2], $ids[3]),
          "EmailAddress" => array($emails[0], $emails[1],$emails[2],$emails[3]),
          "Url" => array($urls[0], $urls[1],$urls[2],$urls[3]),
          "expected" => array(
            'TelephoneNumber' => '["+12 099 123331231 x22","23 221122","22","+12 099 123331231"]',
            'TelephoneNumber:office' => '["+12 099 123331231 x22","+12 099 123331231"]',
            'TelephoneNumber:home' => '["23 221122"]',
            'TelephoneNumber:office:more' => '["+12 099 123331231 x22","+12 099 123331231"]',
            'Garbage' => 'null',
            'Address' => '[]',
            'Address:room' => '["1"]',
            'Address:street' => '["Riverstreet","12 Hillstreet","Riverstreet"]',
            'Address:locality' =>  '["Riverdale","Uptown","Riverdale"]',
            'Address:postal_code' =>  '["11 River","11 River"]',
            'Address:state' => '["Riviera","Upstate","Riviera"]',
            'Address:country' => '["Rivernia","Upper Volta","Switserland","Rivernia"]',
            'Address:home' => '[]',
            'PrimaryName' => '["Godfried Viggo"]',
            'PrimaryName:official' => '["Godfried Viggo"]',
            'Name' => '["Godfried Viggo","Godfried Viggo","Godfried","Godfried of Viggo jr."]',
            'Name:official' => '["Godfried Viggo","Godfried","Godfried of Viggo jr."]',
            'Name:preferred' => '["Godfried Viggo"]',
            'Name:none' => '[]',
            'Identifier' => '["viggo7","viggo7","viggo8","viggo9"]',
            'Identifier:uid' =>  '["viggo7","viggo8"]',
            'Identifier:badge' => '["viggo7"]',
            'Identifier:none' => '[]',
            'EmailAddress' => '["Godfried.Viggo@example.nl","Godfried.Viggo@example.nl","Godfried.Viggo@example.de","Godfried.Viggo@example.dk"]',
            'EmailAddress:official' => '["Godfried.Viggo@example.nl","Godfried.Viggo@example.de"]',
            'EmailAddress:preferred' => '["Godfried.Viggo@example.nl"]',
            'EmailAddress:home' => '["Godfried.Viggo@example.dk"]',
            'Url' =>  '["http:\/\/my.url.com","http:\/\/my.url.com","http:\/\/my.url.com\/me","http:\/\/my.url.com\/you"]',
            'Url:office' =>  '["http:\/\/my.url.com","http:\/\/my.url.com\/me"]',
            'Url:home' =>  '["http:\/\/my.url.com"]',
            'Url:preferred' =>  '["http:\/\/my.url.com\/you"]',
            'Url:none' => '[]' 
          )
        ),
      );

      foreach($combinations as $model) {
        $model['PrimaryName']['primary_name']=true;
        foreach($model['expected'] as $attr=>$expect) {
          $values = json_encode($this->FixedAttributeComponent->getAttribute($model,$attr));
          if(strcmp($expect,$values)) echo "expect $attr = '$values'<br/>";
          $this->assertEquals($expect, $values);
        }
      }
    }


    public function testMatch() {
      // test matching against a know OI model

      $model1 = array(
        "Identifier" => array(
          array(
            "identifier" => "viggo7",
            "type" => "uid"
          )
        )
      );
      $model2 = array(
        "Identifier" => array(
          array(
            "identifier" => "viggo7",
            "type" => "uid"
          ),
          array(
            "identifier" => "viggo8",
            "type" => "uid"
          )
        )
      );
      $model3 = array(
        "Identifier" => array(
          array(
            "identifier" => "viggo7",
            "type" => "uid"
          ),
          array(
            "identifier" => "viggo8",
            "type" => "uid"
          ),
          array(
            "identifier" => "viggo7",
            "type" => "badge"
          )
        )
      );

      $combinations = array(
        "Identifier" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model1
        ),
        "Identifier" => array(
            "except" => true,
            "hash" => hash('sha256','viggo8'),
            "model" => $model1
        ),
        "Identifier:uid" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model1
        ),
        "Identifier:uid" => array(
            "except" => true,
            "hash" => hash('sha256','viggo8'),
            "model" => $model1
        ),
        "Identifier:badge" => array(
            "except" => true,
            "hash" => hash('sha256','viggo7'),
            "model" => $model1
        ),
        "Identifier:badge" => array(
            "except" => true,
            "hash" => hash('sha256','viggo8'),
            "model" => $model1
        ),
        "Identifier" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model2
        ),
        "Identifier" => array(
            "except" => false,
            "hash" => hash('sha256','viggo8'),
            "model" => $model2
        ),
        "Identifier:uid" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model2
        ),
        "Identifier:uid" => array(
            "except" => false,
            "hash" => hash('sha256','viggo8'),
            "model" => $model2
        ),
        "Identifier:badge" => array(
            "except" => true,
            "hash" => hash('sha256','viggo7'),
            "model" => $model2
        ),
        "Identifier:badge" => array(
            "except" => true,
            "hash" => hash('sha256','viggo8'),
            "model" => $model2
        ),
        "Identifier" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model3
        ),
        "Identifier" => array(
            "except" => false,
            "hash" => hash('sha256','viggo8'),
            "model" => $model3
        ),
        "Identifier:uid" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model3
        ),
        "Identifier:uid" => array(
            "except" => false,
            "hash" => hash('sha256','viggo8'),
            "model" => $model3
        ),
        "Identifier:badge" => array(
            "except" => false,
            "hash" => hash('sha256','viggo7'),
            "model" => $model3
        ),
        "Identifier:badge" => array(
            "except" => true,
            "hash" => hash('sha256','viggo8'),
            "model" => $model3
        ),
      );

      foreach($combinations as $test => $model) {
        if($model['except']) {
          $this->expectException("RuntimeException","Not Authorized");
          if($this->FixedAttributeComponent->matchAttribute($model['model'],$test,$model['hash'])) {
            $this->assertEquals("never","matches");
          }
        } else {
          $this->assertTrue($this->FixedAttributeComponent->matchAttribute($model['model'],$test,$model['hash']));
        }
      }
    }

    public function tearDown() {
        parent::tearDown();
        // Clean up after we're done
        unset($this->FixedAttributeComponent);
        //unset($this->Controller);
    }
}
