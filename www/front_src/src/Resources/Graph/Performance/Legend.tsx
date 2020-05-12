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
    width: 9,
    height: 9,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
  },
  legendCaption: {
    marginRight: theme.spacing(1),
  },
}));

interface Props {
  lines: Array<Line>;
  toggable: boolean;
  onItemToggle: (params) => void;
}

const Legend = ({ lines, onItemToggle, toggable }: Props): JSX.Element => {
  const classes = useStyles();

  const getLegendName = ({ metric, name, display }: Line): JSX.Element => {
    if (toggable) {
      const control = (
        <Checkbox
          color="primary"
          size="small"
          checked={display}
          onChange={(_, checked): void => {
            onItemToggle({ checked, metric });
          }}
        />
      );

      return (
        <>
          <FormControlLabel
            control={control}
            label={<Typography variant="body2">{name}</Typography>}
          />
        </>
      );
    }

    return (
      <Typography className={classes.legendCaption} variant="caption">
        {name}
      </Typography>
    );
  };

  return (
    <>
      {lines.map((line) => {
        const { color, name } = line;

        const icon = (
          <div
            className={classes.legendIcon}
            style={{ backgroundColor: color }}
          />
        );

        return (
          <div className={classes.legendItem} key={name}>
            {getLegendName(line)}
            {icon}
          </div>
        );
      })}
    </>
  );
};

export default Legend;
