<?php

namespace App\Services\AzureAD;

use InvalidArgumentException;

/**
 * It validates the provided properties for use of the azure graph api to get specific user properties
 * @link  https://learn.microsoft.com/en-us/graph/api/resources/user?view=graph-rest-1.0#properties
 */
class UserProperties
{

    protected string $properties;

    protected array $validProperties = [
        'aboutMe',
        'accountEnabled',
        'ageGroup',
        'assignedLicenses',
        'assignedPlans',
        'birthday',
        'businessPhones',
        'city',
        'companyName',
        'consentProvidedForMinor',
        'country',
        'createdDateTime',
        'creationType',
        'deletedDateTime',
        'department',
        'displayName',
        'employeeHireDate',
        'employeeLeaveDateTime',
        'employeeId',
        'employeeOrgData',
        'employeeType',
        'externalUserState',
        'externalUserStateChangeDateTime',
        'faxNumber',
        'givenName',
        'hireDate',
        'id',
        'identities',
        'imAddresses',
        'interests',
        'isResourceAccount',
        'jobTitle',
        'lastPasswordChangeDateTime',
        'legalAgeGroupClassification',
        'licenseAssignmentStates',
        'mail',
        'mailboxSettings',
        'mailNickname',
        'mobilePhone',
        'mySite',
        'officeLocation',
        'onPremisesDistinguishedName',
        'onPremisesDomainName',
        'onPremisesExtensionAttributes',
        'onPremisesImmutableId',
        'onPremisesLastSyncDateTime',
        'onPremisesProvisioningErrors',
        'onPremisesSamAccountName',
        'onPremisesSecurityIdentifier',
        'onPremisesSyncEnabled',
        'onPremisesUserPrincipalName',
        'otherMails',
        'passwordPolicies',
        'passwordProfile',
        'pastProjects',
        'postalCode',
        'preferredDataLocation',
        'preferredLanguage',
        'preferredName',
        'provisionedPlans',
        'proxyAddresses',
        'refreshTokensValidFromDateTime',
        'responsibilities',
        'schools',
        'securityIdentifier',
        'showInAddressList',
        'signInSessionsValidFromDateTime',
        'skills',
        'state',
        'streetAddress',
        'surname',
        'usageLocation',
        'userPrincipalName',
        'userType',
    ];

    public function __construct(string|array $properties)
    {
        if (is_string($properties)) {
            $properties = explode(',', $properties);
            $properties = array_map('trim', $properties);
        }

        foreach ($properties as $property) {
            if (!in_array($property, $this->validProperties)) {
                throw new InvalidArgumentException(sprintf('"%s" property is not allowed. For more information, please visit: https://learn.microsoft.com/en-us/graph/api/resources/user?view=graph-rest-1.0#properties', $property));
            }
        }

        $this->properties = implode(',', $properties);
    }

    public function get(): string
    {
        return $this->properties;
    }
}




