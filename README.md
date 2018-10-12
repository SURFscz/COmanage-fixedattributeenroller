# COmanage-fixedattributeenroller
COmanage enrollment plugin that enforces the content and availability of one or more URL specified attributes

This project has the following deployment goals:
- create an Enrollment plugin that allows checking the content of query-paramater provided parameters


Use Case Description
====================
User wants to allow new candidates to use the self-enrollment enrollment flow. Preferably, the user does not
need to enter any information him/herself, but all data is supplied using SAML assertions. 

To prevent the misuse of the self-enrollment invitation, the user would like to check for the content of specific
SAML assertions, specifically the email address.

This plugin allows parsing the return URL parameter for one or more attribute names and SHA256 hashes of their accepted
contents. The SAML assertions for these attributes should yield the same SHA256 hash, or else enrollment is broken off.

Encoding in the return parameter supports encoding an actual return URL as well. If the return parameter is not a URL,
it is assumed that the parameter only encodes key-value pairs. 

The return parameter looks like:

```https://your.domain.tld/your/path?attribute:type=sha256-hash&attribute2:type=sha256-hash```

E.g.:
Email address ```me@example.com```:
```https://www.example.com/welcom?EmailAddress=71e9ce9fd1485f1e79e8d966318cd1bb25472a00ab53f458a7a09fdd15d679d4```
```https://www.example.com/welcom?EmailAddress:office=71e9ce9fd1485f1e79e8d966318cd1bb25472a00ab53f458a7a09fdd15d679d4```

Room number 113:
```https://www.example.com/welcom?Address:room=6df9d0a6ea8e120e383e708daf48c33d8f6d0f79b42bfc2a0fefbb2d092cadee```

And the enrollment URL is then:
```https://comanage.your.domain.tld/registry/co_petitions/start/coef:<id>/return=<base64 encoded url>```

E.g.:
For room number 113:
```https://comanage.example.com/registry/co_petitions/start/coef:12/return=aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20vd2VsY29tP0FkZHJlc3M6cm9vbT02ZGY5ZDBhNmVhOGUxMjBlMzgzZTcwOGRhZjQ4YzMzZDhmNmQwZjc5YjQyYmZjMmEwZmVmYmIyZDA5MmNhZGVlCg==```


Please note that the URL query parameters are all url-decoded. If you want to pass irregular non-alphanumeric characters in
either attribute name or value, you need to url-encode them. However, as attributes, attribute types and SHA256 hashes do not 
contain non-alphanumeric characters, this should not be a problem. This could, however, be used in extensions that rely on the
FixedAttributeComponent's ability to parse a URL and return the parameters contained in the return parameter value.

Supported Attributes
====================
The FixedAttributeEnroller supports the following models and their types:
- EmailAddress
- PrimaryName
- Name
- Url
- Address
- Identifier
- TelephoneNumber

Note that for the Address model, the ```type``` specifier specifies an Address attribute. You cannot match the ```street``` of the 
```official``` address, but you can match any 'street' value of all associated addresses as follows ```Address:street```


Configuration
=============
No configuration is required.

Setup
=====
Checkout or link the plugin code to the `local/Plugin` directory of your COmanage Registry installation. Then 
update the app cache:

```
app/Console/cake cache
```

The FixedAttributeEnroller plugin is now run for every enrollment.

Tests
=====
This plugin comes with a minimal set of unit tests that cover only part of all possible use cases.


Disclaimer
==========
This plugin is provided AS-IS without any claims whatsoever to its functionality.
