import * as React from 'react';

import clsx from 'clsx';

import { Typography, makeStyles, useTheme, fade } from '@material-ui/core';

import { Line } from './models';

const useStyles = makeStyles((theme) => ({
  item: {
    display: 'flex',
    alignItems: 'center',
    margin: theme.spacing(0, 1, 1, 0),
  },
  icon: {
    width: 9,
    height: 9,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
  },
  caption: {
    marginRight: theme.spacing(1),
    color: fade(theme.palette.common.black, 0.6),
  },
  hidden: {
    color: theme.palette.text.disabled,
  },
  toggable: {
    cursor: 'pointer',
    '&:hover': {
      color: theme.palette.common.black,
    },
  },
}));

interface Props {
  lines: Array<Line>;
  toggable: boolean;
  onItemToggle: (params) => void;
  onItemHighlight: (metric) => void;
  onClearItemHighlight: () => void;
}

const Legend = ({
  lines,
  onItemToggle,
  toggable,
  onItemHighlight,
  onClearItemHighlight,
}: Props): JSX.Element => {
  const classes = useStyles();
  const theme = useTheme();

  const getLegendName = ({ metric, name, display }: Line): JSX.Element => {
    return (
      <div
        onMouseEnter={(): void => onItemHighlight(metric)}
        onMouseLeave={(): void => onClearItemHighlight()}
      >
        <Typography
          className={clsx(
            {
              [classes.hidden]: !display,
              [classes.toggable]: toggable,
            },
            classes.caption,
          )}
          variant="body2"
          onClick={(): void => {
            if (!toggable) {
              return;
            }
            onItemToggle(metric);
          }}
        >
          {name}
        </Typography>
      </div>
    );
  };

  return (
    <>
      {lines.map((line) => {
        const { color, name, display } = line;

        const iconBackgroundColor = display
          ? color
          : fade(theme.palette.text.disabled, 0.2);

        return (
          <div className={classes.item} key={name}>
            {getLegendName(line)}
            <div
              className={clsx(classes.icon, { [classes.hidden]: !display })}
              style={{ backgroundColor: iconBackgroundColor }}
            />
          </div>
        );
      })}
    </>
  );
};

export default Legend;
