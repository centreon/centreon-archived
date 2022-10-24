import { find, propEq } from 'ramda';

import { SelectEntry } from '@centreon/ui';

import { Criteria } from '../../Filter/Criterias/models';

interface Parameter {
  action: string;
  id: string;
  type: string;
}

interface ParameterWithFilter {
  action: string;
  criteriaStatus: Criteria | undefined;
}

const baseEndpoint = './api/internal.php?object=centreon_module&';

const buildEndPoint = ({ action, id, type }: Parameter): string => {
  return `${baseEndpoint}action=${action}&id=${id}&type=${type}`;
};

const buildExtensionEndPoint = ({
  action,
  criteriaStatus
}: ParameterWithFilter): string => {
  let params = `${baseEndpoint}action=${action}`;

  if (!criteriaStatus || !criteriaStatus.value) {
    return params;
  }

  const values = criteriaStatus.value as Array<SelectEntry>;

  const installed = !!find(propEq('id', 'INSTALLED'), values);
  const uninstalled = !!find(propEq('id', 'UNINSTALLED'), values);
  const upToDate = !!find(propEq('id', 'UPTODATE'), values);
  const outdated = !!find(propEq('id', 'OUTDATED'), values);

  if (!upToDate && outdated) {
    params += '&updated=false';
  } else if (upToDate && !outdated) {
    params += '&updated=true';
  }

  if (!installed && uninstalled) {
    params += '&installed=false';
  } else if (installed && !uninstalled) {
    params += '&installed=true';
  }

  return params;
};

export { buildEndPoint, buildExtensionEndPoint };
