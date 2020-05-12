import * as React from 'react';
import {
  Typography,
  Checkbox,
  FormControlLabel,
  makeStyles,
} from '@material-ui/core';
import { Line } from './models';

const useStyles = makeStyles((theme) => ({
  legendItem: {
    display: 'flex',
    alignItems: 'center',
    marginRight: theme.spacing(1),
  },
  legendIcon: {
    width: 10,
    height: 10,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
  },
}));

interface Props {
  lines: Array<Line>;
  onItemToggle: (params) => void;
}

const Legend = ({ lines, onItemToggle }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      {lines.map(({ color, name, display, metric }) => (
        <div className={classes.legendItem} key={name}>
          <Typography align="center" variant="caption">
            <FormControlLabel
              control={
                <Checkbox
                  color="primary"
                  size="small"
                  checked={display}
                  onChange={(_, checked): void => {
                    onItemToggle({ checked, metric });
                  }}
                />
              }
              label={<Typography variant="body2">{name}</Typography>}
            />
          </Typography>
          <div
            className={classes.legendIcon}
            style={{ backgroundColor: color }}
          />
        </div>
      ))}
    </>
  );
};

export default Legend;
