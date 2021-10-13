const legacyEndpoint = './api/internal.php';
const translationEndpoint = `${legacyEndpoint}?object=centreon_i18n&action=translation`;
const baseEndpoint = './api/beta';
const userEndpoint = `${baseEndpoint}/configuration/users/current/parameters`;
const parametersEndpoint = `${baseEndpoint}/administration/parameters`;
// const aclEndpoint = `${baseEndpoint}/users/acl/actions`;

const aclEndpoint = 'http://localhost:5151/mock/acl';

export { parametersEndpoint, translationEndpoint, aclEndpoint, userEndpoint };
