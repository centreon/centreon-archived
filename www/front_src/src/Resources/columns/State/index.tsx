import React, { useState, useEffect } from 'react';

import {
  Grid,
  Avatar,
  makeStyles,
  fade,
  Tooltip,
  TableContainer,
  TableRow,
  Paper,
  Table,
  TableHead,
  TableCell,
  TableBody,
} from '@material-ui/core';
import { Person as IconAcknowledged } from '@material-ui/icons';
import { lime, purple } from '@material-ui/core/colors';
import { Skeleton } from '@material-ui/lab';

import IconDowntime from '../icons/Downtime';
import { ColumnProps } from '..';
import {
  labelFixed,
  labelAuthor,
  labelStartTime,
  labelEndTime,
  labelComment,
} from '../../translatedLabels';
import { getData } from '../../api';

const useStyles = makeStyles((theme) => ({
  stateChip: {
    width: theme.spacing(4),
    height: theme.spacing(4),
  },
  acknowledged: {
    backgroundColor: fade(lime[900], 0.1),
    color: lime[900],
  },
  downtime: {
    backgroundColor: fade(purple[500], 0.1),
    color: purple[500],
  },
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
}));

interface DetailsTableProps {
  endpoint: string;
  columns: Array<string>;
}

interface DowntimeDetails {
  author: string;
  fixed: boolean;
  start_time: string;
  end_time: string;
  comment: string;
}

const DetailsTable = <TDetails extends {}>({
  endpoint,
  columns,
}: DetailsTableProps): JSX.Element => {
  const [details, setDetails] = useState<TDetails | null>();

  useEffect(() => {
    getData<TDetails>({ endpoint })
      .then((retrievedDetails) => setDetails(retrievedDetails))
      .catch(() => {
        setDetails(null);
      });
  }, []);

  const loading = details === undefined;
  const error = details === null;
  const success = !loading && !error;

  return (
    <TableContainer component={Paper}>
      <Table size="small">
        <TableHead>
          <TableRow component="head">
            {columns.map((column) => (
              <TableCell>{column}</TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          <TableRow>
            {loading && <Skeleton height={20} animation="wave" />}
            {success &&
              columns.map((column) => <TableCell>{details[column]}</TableCell>)}
            {error && <TableCell>oops!</TableCell>}
          </TableRow>
        </TableBody>
      </Table>
    </TableContainer>
  );
};

const DowntimeDetailsTable = ({ endpoint }): JSX.Element => {
  const columns = [
    labelAuthor,
    labelFixed,
    labelStartTime,
    labelEndTime,
    labelComment,
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

const DowntimeChip = ({ endpoint }): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      title={<DowntimeDetailsTable endpoint={endpoint} />}
      classes={{ tooltip: classes.tooltip }}
    >
      <Avatar className={`${classes.stateChip} ${classes.downtime}`}>
        <IconDowntime />
      </Avatar>
    </Tooltip>
  );
};

const AcknowledgedChip = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Avatar className={`${classes.stateChip} ${classes.acknowledged}`}>
      <IconAcknowledged />
    </Avatar>
  );
};

const StateColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  return (
    <Cell width={80}>
      <Grid container spacing={1}>
        {row.in_downtime && (
          <Grid item>
            <DowntimeChip endpoint={row.downtime_endpoint} />
          </Grid>
        )}
        {row.acknowledged && (
          <Grid item>
            <AcknowledgedChip />
          </Grid>
        )}
      </Grid>
    </Cell>
  );
};

export default StateColumn;
