import * as React from 'react';

import { isNil } from 'ramda';

import { makeStyles, Typography } from '@material-ui/core';

import { getCommandsWithArguments } from './utils';

const useStyles = makeStyles((theme) => ({
  argument: {
    marginRight: theme.spacing(0.5),
  },
  argumentWithValue: {
    display: 'flex',
    marginLeft: theme.spacing(1),
  },
  command: {
    fontWeight: theme.typography.fontWeightBold,
  },
  pipe: {
    marginRight: theme.spacing(1),
  },
  pipedCommand: {
    display: 'flex',
    flexDirection: 'row',
  },
}));

interface Props {
  commandLine: string;
}

const CommandWithArguments = ({ commandLine }: Props): JSX.Element => {
  const classes = useStyles();

  const commands = getCommandsWithArguments(commandLine);

  return (
    <div>
      {commands.map(({ command, arguments: args }, index) => {
        return (
          <div key={command}>
            <div className={classes.pipedCommand}>
              {index > 0 && (
                <Typography className={classes.pipe} variant="body2">
                  |
                </Typography>
              )}
              <Typography className={classes.command} variant="body2">
                {command}
              </Typography>
            </div>
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
                  {!isNil(value) && (
                    <Typography variant="body2">{value}</Typography>
                  )}
                </div>
              );
            })}
          </div>
        );
      })}
    </div>
  );
};

export default CommandWithArguments;
