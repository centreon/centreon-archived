import * as React from 'react';

import {
  Tabs,
  Tab,
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

import {
  labelStatusInformation,
  labelMore,
  labelLess,
  labelDetails,
  labelGraph,
} from '../translatedLabels';
import { DetailsSectionProps } from '.';

const useStyles = makeStyles<Theme, { severityCode?: number }>((theme) => {
  const getStatusBackgroundColor = (severityCode): string =>
    getStatusColors({
      theme,
      severityCode,
    }).backgroundColor;

  return {
    content: {
      padding: 10,
      backgroundColor: theme.palette.background.default,
      height: '100%',
    },
    outputCard: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && {
        borderWidth: 2,
        borderStyle: 'solid',
        borderColor: getStatusBackgroundColor(severityCode),
      }),
    }),
    outputTitle: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && { color: getStatusBackgroundColor(severityCode) }),
    }),
  };
});

interface ExpandableCardProps {
  title: string;
  content: string;
  severityCode?: number;
}

const ExpandableCard = ({
  title,
  content,
  severityCode,
}: ExpandableCardProps): JSX.Element => {
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
    <Card className={classes.outputCard} color="green">
      <CardContent>
        <Typography className={classes.outputTitle} variant="subtitle2">
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

const Body = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles({ details });

  const [selectedTabId, setSelectedTabId] = React.useState(0);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };

  return (
    <>
      <Tabs
        variant="fullWidth"
        value={selectedTabId}
        indicatorColor="primary"
        textColor="primary"
        onChange={changeSelectedTabId}
      >
        <Tab label={labelDetails} />
        <Tab label={labelGraph} />
      </Tabs>
      <div className={classes.content}>
        {selectedTabId === 0 && (
          <>
            <ExpandableCard
              title={labelStatusInformation}
              content={details.output}
              severityCode={details.status.severity_code}
            />
          </>
        )}
      </div>
    </>
  );
};

export default Body;
