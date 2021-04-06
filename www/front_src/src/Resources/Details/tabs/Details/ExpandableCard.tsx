import * as React from 'react';

import {
  Typography,
  Card,
  CardContent,
  Divider,
  CardActions,
  Button,
  makeStyles,
  Theme,
} from '@material-ui/core';
import { CreateCSSProperties } from '@material-ui/core/styles/withStyles';

import { getStatusColors } from '@centreon/ui';

import { labelMore, labelLess } from '../../../translatedLabels';

const useStyles = makeStyles<Theme, { severityCode?: number }>((theme) => {
  const getStatusBackgroundColor = (severityCode): string =>
    getStatusColors({
      severityCode,
      theme,
    }).backgroundColor;

  return {
    card: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && {
        borderColor: getStatusBackgroundColor(severityCode),
        borderStyle: 'solid',
        borderWidth: 2,
      }),
    }),
    title: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && { color: getStatusBackgroundColor(severityCode) }),
    }),
  };
});

interface Props {
  content: string;
  severityCode?: number;
  title: string;
}

const ExpandableCard = ({
  title,
  content,
  severityCode,
}: Props): JSX.Element => {
  const classes = useStyles({ severityCode });

  const [outputExpanded, setOutputExpanded] = React.useState(false);

  const lines = content.split(/\n|\\n/);
  const threeFirstlines = lines.slice(0, 3);
  const lastlines = lines.slice(2, lines.length);

  const toggleOutputExpanded = (): void => {
    setOutputExpanded(!outputExpanded);
  };

  const Line = (line, index): JSX.Element => (
    <Typography component="p" key={`${line}-${index}`} variant="body2">
      {line}
    </Typography>
  );

  return (
    <Card className={classes.card}>
      <CardContent>
        <Typography
          gutterBottom
          className={classes.title}
          color="textSecondary"
          variant="subtitle2"
        >
          {title}
        </Typography>
        {threeFirstlines.map(Line)}
        {outputExpanded && lastlines.map(Line)}
      </CardContent>
      {lastlines.length > 0 && (
        <>
          <Divider />
          <CardActions>
            <Button color="primary" size="small" onClick={toggleOutputExpanded}>
              {outputExpanded ? labelLess : labelMore}
            </Button>
          </CardActions>
        </>
      )}
    </Card>
  );
};

export default ExpandableCard;
