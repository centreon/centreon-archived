import { SelectEntry } from '@centreon/ui';

import { SortOrder } from '../../models';
import {
  labelInstalled,
  labelModule,
  labelNotInstalled,
  labelOutdated,
  labelUpdated,
  labelType,
  labelStatus,
  labelWidget,
} from '../../translatedLabels';

export type CriteriaValue = Array<SelectEntry> | string | [string, SortOrder];

export interface Criteria {
  name: string;
  value?: CriteriaValue;
}

const criteriaValueNameById = {
  INSTALLED: labelInstalled,
  MODULE: labelModule,
  OUTDATED: labelOutdated,
  UPDETED: labelUpdated,
  WIDGET: labelWidget,
  not_installed: labelNotInstalled,
};

const installedId = 'INSTALLED';
const installed = { id: installedId, name: criteriaValueNameById[installedId] };

const notInstalledId = 'not_installed';
const notInstalled = {
  id: notInstalledId,
  name: criteriaValueNameById[notInstalledId],
};

const outdatedId = 'OUTDATED';
const outdated = { id: outdatedId, name: criteriaValueNameById[outdatedId] };

const updatedId = 'UPDETED';
const updated = { id: updatedId, name: criteriaValueNameById[updatedId] };

const widgetId = 'WIDGET';
const widget = { id: widgetId, name: criteriaValueNameById[widgetId] };

const moduleId = 'MODULE';
const module = { id: moduleId, name: criteriaValueNameById[moduleId] };

const selectableStatuses = [installed, notInstalled, updated, outdated];
const selectableTypes = [widget, module];

export interface CriteriaDisplayProps {
  label: string;
  options?: Array<SelectEntry>;
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
