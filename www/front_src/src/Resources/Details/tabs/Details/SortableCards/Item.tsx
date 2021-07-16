import * as React from 'react';

import { DraggableSyntheticListeners } from '@dnd-kit/core';
import { isNil } from 'ramda';
import { useTranslation } from 'react-i18next';

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

const useStyles = makeStyles<Theme, { isDragging: boolean }>({
  tile: ({ isDragging }) => ({
    cursor: isDragging ? 'grabbing' : 'grab',
  }),
});

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
      ...props
    }: Props,
    ref: React.ForwardedRef<HTMLDivElement>,
  ) => {
    const { t } = useTranslation();
    const classes = useStyles({ isDragging });

    const variableXs = (width > 600 ? xs / 2 : xs) as 3 | 6 | 12;

    return (
      <Grid item key={title} xs={variableXs} {...props}>
        <div className={classes.tile} ref={ref}>
          <DetailsCard
            active={active}
            isCustomCard={isCustomCard}
            line={line}
            title={t(title)}
          />
        </div>
      </Grid>
    );
  },
);

export default Item;
