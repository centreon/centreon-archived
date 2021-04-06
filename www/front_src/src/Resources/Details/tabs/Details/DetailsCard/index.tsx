import * as React from 'react';

import { Card, CardContent, Typography, Grid } from '@material-ui/core';

interface Props {
  lines: Array<{ key: string; line: JSX.Element | null }>;
  title: string;
}

const DetailsCard = ({ title, lines }: Props): JSX.Element => {
  return (
    <Card style={{ height: '100%' }}>
      <CardContent>
        <Typography gutterBottom color="textSecondary" variant="subtitle2">
          {title}
        </Typography>
        <Grid container direction="column" spacing={1}>
          {lines.map(({ key, line }) => (
            <Grid item key={key}>
              {line}
            </Grid>
          ))}
        </Grid>
      </CardContent>
    </Card>
  );
};

export default DetailsCard;
