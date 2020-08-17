import * as React from 'react';

import { Card, CardContent, Typography, Grid } from '@material-ui/core';

interface Props {
  title: string;
  lines: Array<{ key: string; line: JSX.Element | null }>;
}

const DetailsCard = ({ title, lines }: Props): JSX.Element => {
  return (
    <Card style={{ height: '100%' }}>
      <CardContent>
        <Typography variant="subtitle2" color="textSecondary" gutterBottom>
          {title}
        </Typography>
        <Grid direction="column" container spacing={1}>
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
