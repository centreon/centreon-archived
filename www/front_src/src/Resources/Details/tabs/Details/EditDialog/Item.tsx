import * as React from 'react';

import { DraggableSyntheticListeners } from '@dnd-kit/core';

import {
  fade,
  Grid,
  GridSize,
  lighten,
  makeStyles,
  Paper,
  Theme,
  Typography,
} from '@material-ui/core';

interface Props {
  isDragging?: boolean;
  listeners?: DraggableSyntheticListeners;
  style?;
  title: string;
  xs?: GridSize;
}

const useStyles = makeStyles<Theme, { isDragging: boolean }>((theme) => ({
  text: {
    width: '100%',
  },
  tile: ({ isDragging }) => ({
    alignItems: 'center',
    backgroundColor: lighten(theme.palette.primary.main, 0.9),
    cursor: isDragging ? 'grabbing' : 'grab',
    display: 'flex',
    height: theme.spacing(4),
    justifyItems: 'center',
  }),
}));

const Item = React.forwardRef(
  (
    { title, xs = 6, isDragging = false, ...props }: Props,
    ref: React.ForwardedRef<HTMLDivElement>,
  ) => {
    const classes = useStyles({ isDragging });

    return (
      <Grid item xs={xs} {...props}>
        <div ref={ref}>
          <Paper className={classes.tile}>
            <Typography align="center" className={classes.text}>
              {title}
            </Typography>
          </Paper>
        </div>
      </Grid>
    );
  },
);

export default Item;
