import { FC, useMemo } from 'react';

import { Grid } from '@visx/visx';
import { scaleLinear } from '@visx/scale';
import { useAtomValue } from 'jotai';

import { useTheme } from '@mui/material';

import { columnsAtom } from '../atoms';

interface Props {
  height: number;
  width: number;
}

const EditionGrid: FC<Props> = ({ width, height }) => {
  const theme = useTheme();

  const columns = useAtomValue(columnsAtom);

  const xScale = useMemo(
    () =>
      scaleLinear({
        domain: [0, 12],
        range: [0, width],
      }),
    [width],
  );

  const tick = 12 / columns;

  const xTickValues = Array(columns)
    .fill(0)
    .map((_, index) => index * tick);

  return (
    <svg style={{ height, position: 'absolute', width }}>
      <Grid.GridColumns
        height={height}
        scale={xScale}
        stroke={theme.palette.divider}
        tickValues={xTickValues}
        width={width}
      />
    </svg>
  );
};

export default EditionGrid;
