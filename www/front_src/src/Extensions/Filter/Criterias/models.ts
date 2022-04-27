import { SelectEntry } from '@centreon/ui';

import {
  labelInstalled,
  labelModule,
  labelUninstalled,
  labelOutdated,
  labelUpToDate,
  labelType,
  labelStatus,
  labelWidget,
} from '../../translatedLabels';

export type CriteriaValue = Array<SelectEntry> | string;

export interface Criteria {
  name: string;
  value?: CriteriaValue;
}

const criteriaValueNameById = {
  INSTALLED: labelInstalled,
  MODULE: labelModule,
  OUTDATED: labelOutdated,
  UNINSTALLED: labelUninstalled,
  UPTODATE: labelUpToDate,
  WIDGET: labelWidget,
};

const installedId = 'INSTALLED';
const installed = { id: installedId, name: criteriaValueNameById[installedId] };

const uninstalledId = 'UNINSTALLED';
const uninstalled = {
  id: uninstalledId,
  name: criteriaValueNameById[uninstalledId],
};

const outdatedId = 'OUTDATED';
const outdated = { id: outdatedId, name: criteriaValueNameById[outdatedId] };

const upToDateId = 'UPTODATE';
const upToDate = { id: upToDateId, name: criteriaValueNameById[upToDateId] };

const widgetId = 'WIDGET';
const widget = { id: widgetId, name: criteriaValueNameById[widgetId] };

const moduleId = 'MODULE';
const module = { id: moduleId, name: criteriaValueNameById[moduleId] };

const selectableStatuses = [installed, uninstalled, upToDate, outdated];
const selectableTypes = [widget, module];

export interface CriteriaDisplayProps {
  label: string;
  options: Array<SelectEntry>;
}

export interface CriteriaById {
  [criteria: string]: CriteriaDisplayProps;
}

export enum CriteriaNames {
  statuses = 'statuses',
  types = 'types',
}

const selectableCriterias: CriteriaById = {
  [CriteriaNames.statuses]: {
    label: labelStatus,
    options: selectableStatuses,
  },
  [CriteriaNames.types]: {
    label: labelType,
    options: selectableTypes,
  },
};

export {
  criteriaValueNameById,
  selectableStatuses,
  selectableCriterias,
  selectableTypes,
};
