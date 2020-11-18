import * as React from 'react';

import { Line, Point, Group, LineShape } from '@visx/visx';
import { Annotation } from 'react-annotation';

import { useTheme, Tooltip } from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

const Annotations = ({ xScale }): JSX.Element => {
  const theme = useTheme();

  // const xDomain = xScale.invert(x - margin.left);

  // const index = bisectDate(getDates(timeSeries), xDomain, 1);

  console.log(xScale(new Date('2020-11-16T17:05:00+01:00')));
  return (
    <>
      {/* <Annotation
        x={0}
        y={0}
        dy={0}
        dx={0}
        // color="#9610ff"
        title="Annotations :)"
        label="Longer text to show text wrapping"
        radius={14}
        text="A"
        
      > */}
      <Tooltip title="plop">
        <IconComment
          fontSize="small"
          viewBox="0 0"
          height={20}
          y={-30}
          x={0}
          color="primary"
        />
      </Tooltip>
      {/* </Annotation> */}
      <Line
        from={{ x: 100, y: 0 }}
        to={{ x: 100, y: 240 }}
        stroke={theme.palette.primary.main}
        strokeWidth={1}
        strokeOpacity={0.6}
        pointerEvents="none"
      />
    </>
  );
};

export default Annotations;
