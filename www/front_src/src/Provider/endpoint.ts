const legacyEndpoint = './api/internal.php';
const translationEndpoint = `${legacyEndpoint}?object=centreon_i18n&action=translation`;
const baseEndpoint = './api/beta';
const userEndpoint = `${baseEndpoint}/configuration/users/current/parameters`;
const parametersEndpoint = `${baseEndpoint}/administration/parameters`;
const aclEndpoint = `${baseEndpoint}/users/acl/actions`;
const platformModulesEndpoint =
  'http://localhost:5001/centreon/platform/versions';

export {
  parametersEndpoint,
  translationEndpoint,
  aclEndpoint,
  userEndpoint,
  platformModulesEndpoint,
};
