import { Theme } from '@material-ui/core';

interface Condition {
  name: string;
  condition;
  color: string;
}

const rowColorConditions = (theme: Theme): Array<Condition> => [
  {
    name: 'inDowntime',
    condition: ({ in_downtime }): boolean => in_downtime,
    color: theme.palette.action.inDowntimeBackground,
  },
  {
    name: 'acknowledged',
    condition: ({ acknowledged }): boolean => acknowledged,
    color: theme.palette.action.acknowledgedBackground,
  },
];

export { rowColorConditions };
