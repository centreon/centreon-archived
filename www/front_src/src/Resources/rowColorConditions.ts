import { lime, purple } from '@material-ui/core/colors';

const rowColorConditions = [
  {
    name: 'inDowntime',
    condition: ({ in_downtime }): boolean => in_downtime,
    color: purple[500],
  },
  {
    name: 'acknowledged',
    condition: ({ acknowledged }): boolean => acknowledged,
    color: lime[900],
  },
];

export default rowColorConditions;
