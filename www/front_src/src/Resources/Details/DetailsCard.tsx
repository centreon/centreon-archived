import * as React from 'react';

import {
  Card,
  CardContent,
  Typography,
  Grid,
  makeStyles,
} from '@material-ui/core';

interface Props {
  title: string;
  lines: Array<{ key: string; line: JSX.Element }>;
}

const DetailsCard = ({ title, lines }: Props): JSX.Element => {
  return (
    <Card>
      <CardContent>
        <Grid container direction="column" spacing={1}>
          <Grid item direction="column" container>
            <Grid item>
              <Typography variant="subtitle2" color="textSecondary">
                {title}
              </Typography>
            </Grid>
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
