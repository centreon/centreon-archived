const legacyEndpoint = './api/internal.php';
const translationEndpoint = `${legacyEndpoint}?object=centreon_i18n&action=translation`;
const baseEndpoint = './api/beta';
const parametersEndpoint = `${baseEndpoint}/parameters`;
const aclEndpoint = `${baseEndpoint}/users/acl/actions`;

export { parametersEndpoint, translationEndpoint, aclEndpoint };
