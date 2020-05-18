const legacyEndpoint = './api/internal.php';
const translationEndpoint = `${legacyEndpoint}?object=centreon_i18n&action=translation`;
const userEndpoint = `${legacyEndpoint}?object=centreon_topcounter&action=user`;
const baseEndpoint = './api/beta';
const aclEndpoint = `${baseEndpoint}/users/acl/actions`;

export { userEndpoint, translationEndpoint, aclEndpoint };
