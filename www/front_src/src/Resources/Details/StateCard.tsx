import * as React from 'react';

import { Card, CardContent, Grid, Typography } from '@material-ui/core';

interface Props {
  title: string;
  contentLines: Array<string>;
  chip: JSX.Element;
}

const Line = (line): JSX.Element => (
  <Typography key={line} variant="body2" component="p">
    {line}
  </Typography>
);

const StateCard = ({ title, contentLines, chip }: Props): JSX.Element => {
  return (
    <Card>
      <CardContent>
        <Grid container>
          <Grid item>
            <Typography>{title}</Typography>
          </Grid>
          <Grid item style={{ flexGrow: 1 }}>
            {contentLines.map(Line)}
          </Grid>
          <Grid item>{chip}</Grid>
        </Grid>
      </CardContent>
    </Card>
  );
};

export default StateCard;
