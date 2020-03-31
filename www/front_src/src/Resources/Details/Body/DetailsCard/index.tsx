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
        <Grid container direction="column" spacing={1}>
          <Grid item>
            <Typography variant="subtitle2" color="textSecondary">
              {title}
            </Typography>
          </Grid>
          <Grid item direction="column" container>
            {lines.map(({ key, line }) => (
              <Grid item key={key}>
                {line}
              </Grid>
            ))}
          </Grid>
        </Grid>
      </CardContent>
    </Card>
  );
};

export default DetailsCard;
