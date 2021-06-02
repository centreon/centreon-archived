import * as React from 'react';

interface Props {
  areaColor: string;
  lineColor: string;
  transparency: number;
  xAnchorPoint: number;
  yAnchorPoint: number;
}

const AnchorPoint = ({
  xAnchorPoint,
  yAnchorPoint,
  areaColor,
  transparency,
  lineColor,
}: Props): JSX.Element => (
  <circle
    cx={xAnchorPoint}
    cy={yAnchorPoint}
    fill={areaColor}
    fillOpacity={1 - transparency * 0.01}
    r={3}
    stroke={lineColor}
  />
);

export default AnchorPoint;
