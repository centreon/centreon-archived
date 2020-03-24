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

import { labelMore, labelLess } from '../../translatedLabels';

const useStyles = makeStyles<Theme, { severityCode?: number }>((theme) => {
  const getStatusBackgroundColor = (severityCode): string =>
    getStatusColors({
      theme,
      severityCode,
    }).backgroundColor;

  return {
    card: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && {
        borderWidth: 2,
        borderStyle: 'solid',
        borderColor: getStatusBackgroundColor(severityCode),
      }),
    }),
    title: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && { color: getStatusBackgroundColor(severityCode) }),
    }),
  };
});

interface Props {
  title: string;
  content: string;
  severityCode?: number;
}

const ExpandableCard = ({
  title,
  content,
  severityCode,
}: Props): JSX.Element => {
  const classes = useStyles({ severityCode });

  const [outputExpanded, setOutputExpanded] = React.useState(false);

  const lines = content.split('\n');
  const threeFirstlines = lines.slice(0, 3);
  const lastlines = lines.slice(3, lines.length - 1);

  const toggleOutputExpanded = (): void => {
    setOutputExpanded(!outputExpanded);
  };

  const Line = (line): JSX.Element => (
    <Typography key={line} variant="body2" component="p">
      {line}
    </Typography>
  );

  return (
    <Card className={classes.card} color="green">
      <CardContent>
        <Typography className={classes.title} variant="subtitle2">
          {title}
        </Typography>
        {threeFirstlines.map(Line)}
        {outputExpanded && lastlines.map(Line)}
      </CardContent>
      {lastlines && (
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
