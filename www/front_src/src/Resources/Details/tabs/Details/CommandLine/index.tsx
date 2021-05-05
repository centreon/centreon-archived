import * as React from 'react';

import { isNil } from 'ramda';

import { makeStyles, Typography } from '@material-ui/core';

import { getCommandWithArguments } from './utils';

const useStyles = makeStyles((theme) => ({
  argument: {
    marginRight: theme.spacing(0.5),
  },
  argumentWithValue: {
    display: 'flex',
    marginLeft: theme.spacing(1),
  },
  command: {
    fontWeight: 'bold',
  },
}));

interface Props {
  commandLine: string;
}

const CommandWithArguments = ({ commandLine }: Props): JSX.Element => {
  const classes = useStyles();

  const { command, arguments: args } = getCommandWithArguments(commandLine);

  return (
    <div>
      <Typography className={classes.command} variant="body2">
        {command}
      </Typography>
      {args.map(([argument, value]) => {
        return (
          <div className={classes.argumentWithValue} key={argument}>
            <Typography
              className={classes.argument}
              color="textSecondary"
              variant="body2"
            >
              {argument}
            </Typography>
            {!isNil(value) && <Typography variant="body2">{value}</Typography>}
          </div>
        );
      })}
    </div>
  );
};

export default CommandWithArguments;
