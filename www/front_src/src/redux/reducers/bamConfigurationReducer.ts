import * as actions from '../actions/bamConfigurationActions';
import { ReduxState } from '.';

export interface BamConfiguration {
  id: string;
  activate: boolean;
  name: string;
  description: string;
  icon: string;
  inherit_kpi_downtimes: boolean;
  additional_poller: Array;
  groups: Array;
  notifications_enabled: boolean;
  bam_contact: Array;
  notification_period: number;
  notification_interval: object;
  notification_options: Array;
  level_w: number;
  level_c: number;
  reporting_timeperiods: Array;
  sla_month_percent_warn: number;
  sla_month_percent_crit: number;
  sla_month_duration_warn: number;
  sla_month_duration_crit: number;
  bam_esc: Array;
  bam_kpi: Array;
  event_handler_enabled: boolean;
  event_handler_command: string;
  event_handler_args: string;
  id_reporting_period: number;
}

interface BamConfigurationState {
  configuration: BamConfiguration;
  errors: object;
}

const initialState = {
  configuration: {
    id: null,
    activate: true,
    name: null,
    description: null,
    icon: null,
    inherit_kpi_downtimes: true,
    additional_poller: [],
    groups: [],
    notifications_enabled: false,
    bam_contact: [],
    notification_period: 1,
    notification_interval: null,
    notification_options: [],
    level_w: 80,
    level_c: 70,
    reporting_timeperiods: [],
    sla_month_percent_warn: null,
    sla_month_percent_crit: null,
    sla_month_duration_warn: null,
    sla_month_duration_crit: null,
    bam_esc: [],
    bam_kpi: [],
    event_handler_enabled: false,
    event_handler_command: null,
    event_handler_args: null,
    id_reporting_period: 1,
  },
  errors: {},
};

const bamConfigurationReducer = (
  state: BamConfigurationState = initialState,
  action: object,
): ReduxState => {
  switch (action.type) {
    case actions.SET_BA_CONFIGURATION:
      return {
        ...state,
        configuration: {
          ...state.configuration,
          ...action.configuration.configuration,
        },
      };
    case actions.SET_BA_CONFIGURATION_ERRORS:
      return { ...state, errors: { ...state.errors, ...action.errors.errors } };
    case actions.REMOVE_BA_CONFIGURATION_ERROR:
      const errors = { ...state.errors, ...action.errors.errors };
      delete errors[action.key];
      return { ...state, errors };
    case actions.RESET_BA_CONFIGURATION_ERRORS:
      return { ...state, errors: {} };
    default:
      return state;
  }
};

export default bamConfigurationReducer;
