import * as React from 'react';

import { DraggableSyntheticListeners } from '@dnd-kit/core';
import { useTranslation } from 'react-i18next';

import { Grid, makeStyles, Paper, Theme } from '@material-ui/core';
import MoreVertIcon from '@material-ui/icons/MoreVert';

import DetailsCard from '../DetailsCard';
import { DetailCardLine } from '../DetailsCard/cards';

interface Props
  extends Pick<DetailCardLine, 'active' | 'line' | 'isCustomCard'> {
  isDragging?: boolean;
  listeners?: DraggableSyntheticListeners;
  style?;
  title: string;
  width: number;
  xs?: number;
}

const useStyles = makeStyles<Theme, { isDragging: boolean }>((theme) => ({
  handler: ({ isDragging }) => ({
    alignItems: 'center',
    cursor: isDragging ? 'grabbing' : 'grab',
    display: 'flex',
    height: '100%',
  }),
  tile: {
    '&:hover': {
      boxShadow: theme.shadows[3],
    },
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
  },
}));

const Item = React.forwardRef(
  (
    {
      title,
      xs = 6,
      isDragging = false,
      width,
      isCustomCard,
      line,
      active,
      listeners,
      ...props
    }: Props,
    ref: React.ForwardedRef<HTMLDivElement>,
  ) => {
    const { t } = useTranslation();
    const classes = useStyles({ isDragging });

    const variableXs = (width > 600 ? xs / 2 : xs) as 3 | 6 | 12;

    return (
      <Grid item key={title} xs={variableXs} {...props}>
        <Paper>
          <div className={classes.tile} ref={ref}>
            <div {...listeners} className={classes.handler}>
              <MoreVertIcon />
            </div>
            <DetailsCard
              active={active}
              isCustomCard={isCustomCard}
              line={line}
              title={t(title)}
            />
          </div>
        </Paper>
      </Grid>
    );
  },
);

export default Item;
