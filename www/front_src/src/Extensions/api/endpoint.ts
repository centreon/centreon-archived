import { find, propEq } from 'ramda';

import { SelectEntry } from '@centreon/ui';

import { Criteria } from '../Filter/Criterias/models';

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
  criteriaStatus,
}: ParameterWithFilter): string => {
  let params = `${baseEndpoint}action=${action}`;

  if (!criteriaStatus || !criteriaStatus.value) {
    return params;
  }

  const values = criteriaStatus.value as Array<SelectEntry>;

  const installed = !!find(propEq('id', 'INSTALLED'), values);
  const notInstalled = !!find(propEq('id', 'NOTINSTALLED'), values);
  const updated = !!find(propEq('id', 'UPDATED'), values);
  const outdated = !!find(propEq('id', 'OUTDATED'), values);

  if (!updated && outdated) {
    params += '&updated=false';
  } else if (updated && !outdated) {
    params += '&updated=true';
  }

  if (!installed && notInstalled) {
    params += '&installed=false';
  } else if (installed && !notInstalled) {
    params += '&installed=true';
  }

  return params;
};

export { buildEndPoint, buildExtensionEndPoint };
